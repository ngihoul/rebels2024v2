<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventAttendee;
use App\Entity\User;
use App\Form\AttendeeType;
use App\Form\EventType;
use App\Form\InvitationType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/agenda', name: 'app_agenda')]
    public function index(): Response
    {
        return $this->render('agenda/index.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }

    #[Route('/create-event', name: 'app_create_event')]
    public function createEvent(Request $request): Response
    {
        try {
            $event = new Event();

            $form = $this->createForm(EventType::class, $event);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $startDate = $form->get('date')->getData();
                $endDate = $form->get('end_date')->getData();
                $frequency = $form->get('frequency')->getData();

                $this->entityManager->beginTransaction();

                if ($endDate === null) {
                    $this->entityManager->persist($event);
                } else {
                    $currentDate = $startDate;
                    while ($currentDate <= $endDate) {
                        $newEvent = clone $event;
                        $newEvent->setDate($currentDate);

                        $this->entityManager->persist($newEvent);

                        if ($frequency === 'daily') {
                            $currentDate = $currentDate->modify('+1 day');
                        } elseif ($frequency === 'weekly') {
                            $currentDate = $currentDate->modify('+1 week');
                        } elseif ($frequency === 'biweekly') {
                            $currentDate = $currentDate->modify('+2 weeks');
                        } elseif ($frequency === 'monthly') {
                            $currentDate = $currentDate->modify('+1 month');
                        }
                    }
                }

                $this->entityManager->flush();
                $this->entityManager->commit();

                $this->addFlash('success', 'L\'évènement a bien été créé.');
                return $this->redirectToRoute('app_agenda');
            }
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            $this->addFlash('error', 'Une erreur s\'est produite lors de la création de l\'événement.');
            return $this->redirectToRoute('app_agenda');
        }

        return $this->render('agenda/event_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/invitation/{id}', name: 'app_invitation')]
    public function inviteUsers(Request $request, Event $event): Response
    {
        try {
            if (!$event) {
                throw new EntityNotFoundException('L\'évèment n\'existe pas');
            }

            $form = $this->createForm(InvitationType::class, null, ['event' => $event]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $invitedTeams = $form->get('invitedTeams')->getData();
                $invitedUsers = $form->get('invitedUsers')->getData();

                foreach ($invitedTeams as $team) {
                    foreach ($team->getPlayers() as $player) {
                        $invitedUsers[] = $player;
                    }
                }

                foreach ($invitedUsers as $user) {
                    // Check if the user is already invited to the event
                    $existingAttendee = $this->entityManager
                        ->getRepository(EventAttendee::class)
                        ->findOneBy(['event' => $event, 'user' => $user]);

                    // If not, create and persist a new EventAttendee instance
                    if (!$existingAttendee) {
                        $eventAttendee = new EventAttendee();
                        $eventAttendee->setEvent($event);
                        $eventAttendee->setUser($user);
                        $eventAttendee->setCreatedAt(new \DateTimeImmutable());

                        $this->entityManager->persist($eventAttendee);
                    }
                }

                $this->entityManager->flush();

                // Send a message to User

                return $this->redirectToRoute('app_agenda');
            }

            return $this->render('agenda/invitation_form.html.twig', [
                'event' => $event,
                'form' => $form->createView(),
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_agenda');
        }
    }
}
