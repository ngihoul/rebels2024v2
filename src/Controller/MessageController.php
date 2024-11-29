<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\MessageStatus;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use App\Repository\MessageStatusRepository;
use App\Service\EmailManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/messages')]
class MessageController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private MessageRepository $messageRepository;
    private MessageStatusRepository $messageStatusRepository;
    private  EmailManager $emailManager;
    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, MessageRepository $messageRepository, MessageStatusRepository $messageStatusRepository, EmailManager $emailManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->messageRepository = $messageRepository;
        $this->messageStatusRepository = $messageStatusRepository;
        $this->emailManager = $emailManager;
        $this->translator = $translator;
    }

    // Display all messages for the user depending his role
    #[Route('/', name: 'app_messages')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        // If User is admin, all messages are displayed, if not, only messages sent to him or write by him (only for coaches) are displayed
        $user = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN') ? true : false;
        $messages = $this->messageRepository->getShortMessages($user, $isAdmin);

        $messagesCount = count($messages);

        return $this->render('message/index.html.twig', [
            'messages' => $messages,
            'messageCount' => $messagesCount
        ]);
    }

    // Create a new message
    #[Route('/create', name: 'app_message_create')]
    #[IsGranted('ROLE_COACH')]
    public function create(Request $request): Response
    {
        $action = 'create';

        try {
            $message = new Message();
            $form = $this->createForm(MessageType::class, $message);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $message = $form->getData();
                $message->setSender($this->getUser());

                $this->entityManager->persist($message);
                $this->entityManager->flush();

                // Save english data
                $titleEnglish = $form->get('titleEnglish')->getData();
                $contentEnglish = $form->get('contentEnglish')->getData();

                $message->setTranslatableLocale('en');

                if ($titleEnglish) {
                    $message->setTitle($titleEnglish);
                }

                if ($contentEnglish) {
                    $message->setContent($contentEnglish);
                }

                $this->entityManager->persist($message);
                $this->entityManager->flush();

                // Get the locale of request
                $locale = $request->getLocale();
                $message->setTranslatableLocale($locale);

                // Send message to users
                $this->sendMessage($form, $message);

                $this->addFlash('success', $this->translator->trans('success.message.created'));
                return $this->redirectToRoute('app_messages');
            }

            return $this->render('message/form.html.twig', [
                'action' => $action,
                'form' => $form
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_messages');
        }
    }

    // Update an existing message
    #[Route('/update/{messageId}', name: 'app_message_update')]
    #[IsGranted('ROLE_COACH')]
    public function update(Request $request, $messageId): Response
    {
        $action = 'update';

        try {
            $message = $this->findMessage($messageId);

            $form = $this->createForm(MessageType::class, $message);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $message = $form->getData();
                $message->setSender($this->getUser());

                $this->entityManager->persist($message);

                $this->sendMessage($form, $message);

                $this->addFlash('success', $this->translator->trans('success.message.updated'));
                return $this->redirectToRoute('app_messages');
            }

            return $this->render('message/form.html.twig', [
                'action' => $action,
                'form' => $form
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_messages');
        }
    }

    // Display message detail
    #[Route('/{messageId}', name: 'app_message_detail')]
    #[IsGranted('ROLE_USER')]
    public function detail($messageId): Response
    {
        try {
            $message = $this->findMessage($messageId);

            // Mark message as read
            $messageStatus = $this->messageStatusRepository->findOneBy(['message' => $message, 'receiver' => $this->getUser()]);

            if ($messageStatus) {
                $messageStatus->setStatus(true);

                $this->entityManager->persist($messageStatus);
                $this->entityManager->flush();
            }

            return $this->render('message/detail.html.twig', [
                "message" => $message
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_messages');
        }
    }

    // Archive an existing message
    #[Route('/archive/{messageId}', name: 'app_message_archive')]
    #[IsGranted('ROLE_COACH')]
    public function archive(Request $request, string $messageId): Response
    {
        try {
            $message = $this->findMessage($messageId);

            $message->setIsArchived(true);
            $this->entityManager->persist($message);
            $this->entityManager->flush();

            // Back to the last page
            $route = $request->headers->get('referer');
            return $this->redirect($route);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_messages');
        }
    }

    // Find a message
    private function findMessage($messageId)
    {
        $message = $this->messageRepository->findOneBy(['id' => $messageId]);

        if (!$message) {
            throw new EntityNotFoundException($this->translator->trans('error.message.not_found'));
        }

        return $message;
    }

    // Send message (and mail) to users
    private function sendMessage($form, Message $message)
    {
        $sentToTeams = $form->get('sentToTeams')->getData();
        $sentToUsers = $form->get('sentToUsers')->getData();

        // Consolidate players from teams into sentToUsers
        foreach ($sentToTeams as $team) {
            foreach ($team->getPlayers() as $player) {
                if (!in_array($player, $sentToUsers->toArray(), true)) {
                    $sentToUsers[] = $player;
                }
            }
        }

        $isSentByMail = $form->get('sent_by_mail')->getData();

        foreach ($sentToUsers as $user) {
            if ($this->messageStatusRepository->findOneBy(['message' => $message, 'receiver' => $user])) {
                continue; // Skip if user already received the message
            }

            $this->handleUserMessage($message, $user);

            if ($isSentByMail) {
                $this->sendAppropriateMail($user, $message);
            }
        }

        $this->entityManager->flush();
    }

    private function handleUserMessage(Message $message, $user)
    {
        if ($user->getAge() < 16) {
            $this->createMessageForParents($message, $user);
        } elseif ($user->getAge() < 18) {
            $this->createMessageForParents($message, $user);
            $this->persistMessageStatus($message, $user);
        } else {
            $this->persistMessageStatus($message, $user);
        }
    }

    private function createMessageForParents(Message $message, $user)
    {
        foreach ($user->getParents() as $parent) {
            $this->persistMessageStatus($message, $parent);
        }
    }

    private function persistMessageStatus(Message $message, $receiver)
    {
        $messageStatus = $this->createMessageStatus($receiver, $message);
        $this->entityManager->persist($messageStatus);
    }

    private function sendAppropriateMail($user, Message $message)
    {
        if ($user->getAge() < 16) {
            // Send email only to parents
            foreach ($user->getParents() as $parent) {
                $this->sendMail($parent->getEmail(), $message);
            }
        } elseif ($user->getAge() < 18) {
            // Send email to parents and optionally to the user (if eligible)
            foreach ($user->getParents() as $parent) {
                $this->sendMail($parent->getEmail(), $message);
            }

            if ($this->canSendEmailToUser($user)) {
                $this->sendMail($user->getEmail(), $message);
            }
        } else {
            // Send email only to the user
            $this->sendMail($user->getEmail(), $message);
        }
    }

    private function canSendEmailToUser($user): bool
    {
        // Check if email exists in the database and if the user can use the app
        return !empty($user->getEmail()) && $user->canUseApp();
    }

    private function sendMail($email, Message $message)
    {
        $this->emailManager->sendEmail(
            $email,
            $this->translator->trans('message.subject', [], 'emails'),
            'message',
            ['message' => $message]
        );
    }

    private function createMessageStatus($user, $message)
    {
        $messageStatus = new MessageStatus();
        $messageStatus->setReceiver($user);
        $messageStatus->setMessage($message);

        return $messageStatus;
    }
}
