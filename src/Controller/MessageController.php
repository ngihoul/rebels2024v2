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

#[Route('/messages')]
#[IsGranted('ROLE_USER')]
class MessageController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private MessageRepository $messageRepository;
    private MessageStatusRepository $messageStatusRepository;
    private  EmailManager $emailManager;

    public function __construct(EntityManagerInterface $entityManager, MessageRepository $messageRepository, MessageStatusRepository $messageStatusRepository, EmailManager $emailManager)
    {
        $this->entityManager = $entityManager;
        $this->messageRepository = $messageRepository;
        $this->messageStatusRepository = $messageStatusRepository;
        $this->emailManager = $emailManager;
    }

    #[Route('/', name: 'app_messages')]
    public function index(): Response
    {
        // If User is admin, all messages are displayed, if not, only messages sent to him are displayed
        $user = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN') ? true : false;
        $messages = $this->messageRepository->getShortMessages($user, $isAdmin);

        $messagesCount = count($messages);

        // dd($messages);

        return $this->render('message/index.html.twig', [
            'messages' => $messages,
            'messageCount' => $messagesCount
        ]);
    }

    #[Route('/create', name: 'app_message_create')]
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

                $titleEnglish = $form->get('titleEnglish')->getData();
                $contentEnglish = $form->get('contentEnglish')->getData();

                $message->setTranslatableLocale('en');
                $message->setTitle($titleEnglish);
                $message->setContent($contentEnglish);

                $this->entityManager->persist($message);
                $this->entityManager->flush();

                // Send message to users
                $this->sendMessage($form, $message);

                $this->addFlash('success', 'Message créé');
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

    #[Route('/update/{messageId}', name: 'app_message_update')]
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

                $this->addFlash('success', 'Message modifié');
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

    #[Route('/{messageId}', name: 'app_message_detail')]
    public function detail($messageId): Response
    {
        try {
            $message = $this->findMessage($messageId);

            // Mark message as read
            $messageStatus = $this->messageStatusRepository->findOneBy(['message' => $message, 'receiver' => $this->getUser()]);

            // Message can be not sent to an admin so no MessageStatus exist
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

    #[Route('/archive/{messageId}', name: 'app_message_archive')]
    public function archive(Request $request, $messageId): Response
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

    private function findMessage($messageId)
    {
        $message = $this->messageRepository->findOneBy(['id' => $messageId]);

        if (!$message) {
            throw new EntityNotFoundException('Message non trouvé');
        }

        return $message;
    }

    private function sendMessage($form, Message $message)
    {
        $sentToTeams = $form->get('sentToTeams')->getData();
        $sentToUsers = $form->get('sentToUsers')->getData();

        foreach ($sentToTeams as $team) {
            foreach ($team->getPlayers() as $player) {
                // Only add player only if he doesn't belong to a team
                if (!in_array($player, $sentToUsers->toArray(), true)) {
                    $sentToUsers[] = $player;
                }
            }
        }

        foreach ($sentToUsers as $user) {
            // Check if users has already received the message. If yes, don't send him again
            $existingReceiver = $this->messageStatusRepository->findOneBy(['message' => $message, 'receiver' => $user]);

            if (!$existingReceiver) {
                $messageStatus = new MessageStatus();
                $messageStatus->setReceiver($user);
                $messageStatus->setMessage($message);

                $this->entityManager->persist($messageStatus);

                // Send Mail if asked
                $isSentByMail = $form->get('sent_by_mail')->getData();

                if ($isSentByMail) {
                    $this->emailManager->sendEmail($user->getEmail(), 'Message reçu', 'message', ['message' => $message]);
                }
            }
        }

        $this->entityManager->flush();
    }
}
