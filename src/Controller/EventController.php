<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventAttendee;
use App\Form\EventType;
use App\Form\InvitationType;
use App\Repository\EventAttendeeRepository;
use App\Repository\EventRepository;
use App\Service\EmailManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agenda')]
class EventController extends AbstractController
{
    private $entityManager;
    private $eventRepository;

    public function __construct(EntityManagerInterface $entityManager, EventRepository $eventRepository)
    {
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
    }

    #[Route('/{page<\d+>?1}', name: 'app_agenda')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, PaginatorInterface $paginator, Security $security): Response
    {
        $EVENT_PER_PAGE = 6;

        $user = $this->getUser();
        $page = (int) $request->get('page');

        if ($security->isGranted('ROLE_COACH')) {
            $futureEvents = $this->eventRepository->findAll();
        } else {
            // Fetch future events which the user is invited to (with reply)
            $futureEvents = $this->eventRepository->findFutureEventsForThisUser($user);
        }

        $futureEventsPaginated = $paginator->paginate(
            $futureEvents,
            $page,
            $EVENT_PER_PAGE
        );

        // Fetch future events which the user is invited to (without reply)
        $pendingEvents = $this->eventRepository->findPendingEventsForThisUser($user);

        return $this->render('agenda/index.html.twig', [
            'futureEvents' => $futureEventsPaginated,
            'pendingEvents' => $pendingEvents,
        ]);
    }

    #[Route('/event/{id}', name: 'app_agenda_detail')]
    #[IsGranted('ROLE_USER')]
    public function detail(Event $event): Response
    {
        $attendees = 0;
        $awaiting = 0;
        $unavailable = 0;

        foreach ($event->getAttendees() as $attendee) {
            if (NULL === $attendee->isUserResponse()) {
                $awaiting++;
            } elseif (true === $attendee->isUserResponse()) {
                $attendees++;
            } elseif (false === $attendee->isUserResponse()) {
                $unavailable++;
            }
        }

        return $this->render('agenda/detail.html.twig', [
            'event' => $event,
            'attendees' => $attendees,
            'awaiting' => $awaiting,
            'unavailable' => $unavailable
        ]);
    }

    #[Route('/event/{id}/attendance', name: 'app_agenda_attendance')]
    #[IsGranted('ROLE_COACH')]
    public function attendance(Event $event): Response
    {
        return $this->render('agenda/attendance.html.twig', [
            'event' => $event
        ]);
    }

    #[Route('/create', name: 'app_create_event')]
    #[IsGranted('ROLE_COACH')]
    public function create(Request $request): Response
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

                // Check if the event is a single-day or recurring event
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

    #[Route('/update/{id}', name: 'app_agenda_update')]
    #[IsGranted('ROLE_COACH')]
    public function update(Request $request, Event $event): Response
    {
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($event);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_agenda');
        }

        return $this->render('agenda/event_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/invitation/{id}', name: 'app_agenda_invitation')]
    #[IsGranted('ROLE_COACH')]
    public function invite(Request $request, EmailManager $emailManager, EventAttendeeRepository $eventAttendeeRepository): Response
    {
        try {
            $eventId = $request->get('id');
            $event = $this->eventRepository->find($eventId);

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
                    $existingAttendee = $eventAttendeeRepository->findOneBy(['event' => $event, 'user' => $user]);

                    // If not, create and persist a new EventAttendee
                    if (!$existingAttendee) {
                        $eventAttendee = new EventAttendee();
                        $eventAttendee->setEvent($event);
                        $eventAttendee->setUser($user);
                        $eventAttendee->setCreatedAt(new \DateTimeImmutable());

                        $this->entityManager->persist($eventAttendee);

                        // Send a message to User
                        $emailManager->sendEmail($user->getEmail(), 'Invitation a un évènement', 'invitation_confirmation', ['event' => $event]);
                    }
                }

                $this->entityManager->flush();

                return $this->redirect($request->request->get('referer'));
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

    #[Route('/invitation/{id}/{result}', name: 'app_agenda_response')]
    #[IsGranted('ROLE_USER')]
    public function response(Request $request, EventAttendeeRepository $eventAttendeeRepository): Response
    {
        try {
            $eventId = $request->get('id');
            $event = $this->eventRepository->find($eventId);
            if (!$event) {
                throw new EntityNotFoundException('L\'évènement n\'existe pas.');
            }

            $response = $request->get('result');

            $eventAttendee = $eventAttendeeRepository->findOneBy(['user' => $this->getUser(), 'event' => $event]);
            if (!$eventAttendee) {
                throw new EntityNotFoundException('L\'invitation à l\'évènement n\'a pas été trouvée.');
            }

            if ($response === 'accept') {
                $eventAttendee->setUserResponse(true);
                $messageType = 'success';
                $message = 'Tu es bien inscrit à l\'évènement ' . $event->getName();
            } elseif ($response === 'decline') {
                $eventAttendee->setUserResponse(false);
                $messageType = 'error';
                $message = 'Tu as refusé l\'invitation à l\'évènement ' . $event->getName();
            }

            $eventAttendee->setRespondedAt(new \DateTimeImmutable());

            $this->entityManager->persist($eventAttendee);
            $this->entityManager->flush();

            $this->addFlash($messageType, $message);

            $route = $request->headers->get('referer');
            return $this->redirect($route);
        } catch (EntityNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_agenda');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur s\'est produite lors du traitement de la réponse à l\'invitation.');
            return $this->redirectToRoute('app_agenda');
        }
    }

    #[Route('/delete/{id}', name: 'app_agenda_delete')]
    #[IsGranted('ROLE_COACH')]
    public function delete(Request $request, Event $event): Response
    {
        try {
            if (!$event->getAttendees()->isEmpty()) {
                throw new Exception('Impossible de supprimer un évènement avec invitation');
            }
            $this->entityManager->remove($event);
            $this->entityManager->flush();

            $this->addFlash('success', 'L\'évènement a bien été supprimé');
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        $route = $request->headers->get('referer');
        return $this->redirect($route);
    }
}
