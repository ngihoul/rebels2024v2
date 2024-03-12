<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\MessageStatus;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    public function __construct(EntityManagerInterface $entityManager, MessageRepository $messageRepository)
    {
        $this->entityManager = $entityManager;
        $this->messageRepository = $messageRepository;
    }

    #[Route('/', name: 'app_messages')]
    public function index(): Response
    {
        // If User is admin, all messages are displayed, if not, only messages sent to him are displayed
        $user = $this->isGranted('ROLE_ADMIN') ? null : $this->getUser();
        $messages = $this->messageRepository->getShortMessages($user);

        return $this->render('message/index.html.twig', [
            'messages' => $messages,
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

                // Send message to users
                $sentToTeams = $form->get('sentToTeams')->getData();
                $sentToUsers = $form->get('sentToUsers')->getData();

                foreach ($sentToTeams as $team) {
                    foreach ($team->getPlayers() as $player) {
                        $sentToUsers[] = $player;
                    }
                }

                foreach ($sentToUsers as $user) {
                    $messageStatus = new MessageStatus();
                    $messageStatus->setReceiver($user);
                    $messageStatus->setMessage($message);

                    $this->entityManager->persist($messageStatus);
                }

                $this->entityManager->flush();

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
    public function update($messageId): Response
    {
        $action = 'update';

        return $this->render('message/form.html.twig', [
            'action' => $action
        ]);
    }

    #[Route('/{messageId}', name: 'app_message_detail')]
    public function detail($messageId): Response
    {
        $message = $this->messageRepository->findOneBy(['id' => $messageId]);
        return $this->render('message/detail.html.twig', [
            "message" => $message
        ]);
    }
}
