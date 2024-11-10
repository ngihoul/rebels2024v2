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
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/agenda')]
class EventController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private EventRepository $eventRepository;
    private TranslatorInterface $translator;
    private EmailManager $emailManager;

    public function __construct(EntityManagerInterface $entityManager, EventRepository $eventRepository, TranslatorInterface $translator, EmailManager $emailManager)
    {
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
        $this->translator = $translator;
        $this->emailManager = $emailManager;
    }

    // Display events for user with pagination
    #[Route('/{page<\d+>?1}', name: 'app_agenda')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, PaginatorInterface $paginator, Security $security): Response
    {
        $EVENTS_PER_PAGE = 6;

        $user = $this->getUser();
        $page = (int) $request->get('page');

        // For COACH & ADMIN
        if ($security->isGranted('ROLE_COACH')) {
            // Fetch all events
            $futureEvents = $this->eventRepository->findAll();
        } else {
            // Fetch future events which the user is invited to (with reply)
            $futureEvents = $this->eventRepository->findFutureEventsForThisUser($user);
        }

        $futureEventsPaginated = $paginator->paginate(
            $futureEvents,
            $page,
            $EVENTS_PER_PAGE
        );

        // Fetch future events which the user is invited to (without reply)
        $pendingEvents = $this->eventRepository->findPendingEventsForThisUser($user);

        return $this->render('event/index.html.twig', [
            'futureEvents' => $futureEventsPaginated,
            'pendingEvents' => $pendingEvents,
        ]);
    }

    // Display event detail
    #[Route('/event/{id}', name: 'app_agenda_detail')]
    #[IsGranted('ROLE_USER')]
    public function detail(Request $request): Response
    {
        try {
            $eventId = $request->get('id');
            $event = $this->findEvent($eventId);

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

            return $this->render('event/detail.html.twig', [
                'event' => $event,
                'attendees' => $attendees,
                'awaiting' => $awaiting,
                'unavailable' => $unavailable
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_agenda');
        }
    }

    // Display invitation response summary
    #[Route('/event/{id}/attendance', name: 'app_agenda_attendance')]
    #[IsGranted('ROLE_COACH')]
    public function attendance(Request $request): Response
    {
        try {
            $eventId = $request->get('id');
            $event = $this->findEvent($eventId);

            return $this->render('event/attendance.html.twig', [
                'event' => $event
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_agenda');
        }
    }

    // Create a new event
    #[Route('/create', name: 'app_create_event')]
    #[IsGranted('ROLE_COACH')]
    public function create(Request $request): Response
    {
        try {
            $action = 'create';
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
                    // Creating mrecurring event according to the frequency select by the user
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

                $this->addFlash('success', $this->translator->trans('success.event.created'));
                return $this->redirectToRoute('app_agenda');
            }
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            $this->addFlash('error', $this->translator->trans('error.event.creation'));
            return $this->redirectToRoute('app_agenda');
        }

        return $this->render('event/event_form.html.twig', [
            'form' => $form->createView(),
            'action' => $action
        ]);
    }

    // Update an existing event
    #[Route('/update/{id}', name: 'app_agenda_update')]
    #[IsGranted('ROLE_COACH')]
    public function update(Request $request): Response
    {
        // Only one event can be updated. Not possible to update recurring event in batch.
        $action = 'update';

        try {
            $eventId = $request->get('id');
            $event = $this->findEvent($eventId);

            $form = $this->createForm(EventType::class, $event);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->persist($event);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('success.event.update'));
                return $this->redirectToRoute('app_agenda');
            }

            return $this->render('event/event_form.html.twig', [
                'form' => $form->createView(),
                'action' => $action
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_agenda');
        }
    }

    // Invite players to an existing event
    #[Route('/invitation/{id}', name: 'app_agenda_invitation')]
    #[IsGranted('ROLE_COACH')]
    public function invite(Request $request, EventAttendeeRepository $eventAttendeeRepository): Response
    {
        try {
            $eventId = $request->get('id');
            $event = $this->findEvent($eventId);

            $form = $this->createForm(InvitationType::class, null, ['event' => $event]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $invitedTeams = $form->get('invitedTeams')->getData();
                $invitedUsers = $form->get('invitedUsers')->getData();

                // Add players from teams to invited Users Array
                foreach ($invitedTeams as $team) {
                    foreach ($team->getPlayers() as $player) {
                        $invitedUsers[] = $player;
                    }
                }

                // Invite all players to the event
                foreach ($invitedUsers as $user) {
                    // Check if the user is already invited to the event
                    $existingAttendee = $eventAttendeeRepository->findOneBy(['event' => $event, 'user' => $user]);

                    // If not, create and persist a new EventAttendee
                    if (!$existingAttendee) {
                        $eventAttendee = new EventAttendee();
                        $eventAttendee->setEvent($event);
                        $eventAttendee->setUser($user);

                        $this->entityManager->persist($eventAttendee);

                        // TODO : fetch locale from $user

                        // Send an email to User and/or parent
                        $userAge = $user->getAge();

                        if ($userAge < 16) {
                            $this->sendEventInvitationToParents($user, $event);
                        } else if ($userAge >= 16 && $userAge < 18) {
                            $this->sendEventInvitationToUser($user, $event);
                            $this->sendEventInvitationToParents($user, $event);
                        } else {
                            $this->sendEventInvitationToUser($user, $event);
                        }
                    }
                }

                $this->entityManager->flush();

                return $this->redirect($request->request->get('referer'));
            }

            return $this->render('event/invitation_form.html.twig', [
                'event' => $event,
                'form' => $form->createView(),
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_agenda');
        }
    }

    // Answer to an invitation
    #[Route('/invitation/{id}/{result}', name: 'app_agenda_response')]
    #[IsGranted('ROLE_USER')]
    public function response(Request $request, EventAttendeeRepository $eventAttendeeRepository): Response
    {
        try {
            $eventId = $request->get('id');
            $event = $this->findEvent($eventId);

            $response = $request->get('result');

            // Fetch the invitation for this user and this event
            $eventAttendee = $eventAttendeeRepository->findOneBy(['user' => $this->getUser(), 'event' => $event]);
            if (!$eventAttendee) {
                throw new EntityNotFoundException($this->translator->trans('error.invitation_not_found'));
            }

            // Process the reply from the user
            if ($response === 'accept') {
                $eventAttendee->setUserResponse(true);
                $messageType = 'success';
                $message = $this->translator->trans('success.event.accept', ['name' => $event->getName()]);
            } elseif ($response === 'decline') {
                $eventAttendee->setUserResponse(false);
                $messageType = 'error';
                $message = $this->translator->trans('success.event.decline', ['name' => $event->getName()]);
            }

            $eventAttendee->setRespondedAt(new \DateTimeImmutable());

            $this->entityManager->persist($eventAttendee);
            $this->entityManager->flush();

            $this->addFlash($messageType, $message);

            // Back to the previous page
            $route = $request->headers->get('referer');
            return $this->redirect($route);
        } catch (EntityNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_agenda');
        } catch (\Exception $e) {
            $this->addFlash('error', $this->translator->trans('error.event.response'));
            return $this->redirectToRoute('app_agenda');
        }
    }

    // Delet or cancel an existing event
    #[Route('/delete/{id}', name: 'app_agenda_delete')]
    #[IsGranted('ROLE_COACH')]
    public function delete(Request $request, EmailManager $emailManager, Event $event): Response
    {
        try {
            // Cancel (status change) the event if players already invited. If no, delete (from db) the event
            if (!$event->getAttendees()->isEmpty()) {
                // Change status is_cancelled to true
                $event->setIsCancelled(true);

                // Send mail to all invited players
                foreach ($event->getAttendees() as $attendee) {
                    $user = $attendee->getUser();
                    $userAge = $user->getAge();

                    if ($userAge < 16) {
                        $this->sendEventCancellationMailToParents($user, $event);
                    } else if ($userAge >= 16 && $userAge < 18) {
                        $this->sendEventCancellationMailToUser($user, $event);
                        $this->sendEventCancellationMailToParents($user, $event);
                    } else {
                        $this->sendEventCancellationMailToUser($user, $event);
                    }
                }

                $this->addFlash('success', $this->translator->trans('success.event.cancelled'));
            } else {
                // Delete the event from db
                $this->entityManager->remove($event);
                $this->addFlash('success', $this->translator->trans('success.event.delete'));
            }

            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        $route = $request->headers->get('referer');
        return $this->redirect($route);
    }

    // Find a licence from licence id
    private function findEvent(string $eventId)
    {
        $event = $this->eventRepository->find($eventId);
        if (!$event) {
            throw new EntityNotFoundException($this->translator->trans('error.event.not_found'));
        }

        return $event;
    }

    // Send an event invitation email to user
    private function sendEventInvitationToUser($user, $event): void
    {
        if ($user->getEmail()) {
            $this->emailManager->sendEmail($user->getEmail(), $this->translator->trans('event.invitation.user.subject', [], 'emails'), 'invitation_confirmation_user', ['event' => $event]);
        }
    }

    // Send an event invitation email to parents
    private function sendEventInvitationToParents($user, $event): void
    {
        foreach ($user->getParents() as $parent) {
            $this->emailManager->sendEmail($parent->getEmail(), $this->translator->trans('event.invitation.parent.subject', [], 'emails'), 'invitation_confirmation_parent', ['child_firstname' => $user->getFirstname(), 'event' => $event]);
        }
    }

    // Send an event cancellation email to user
    private function sendEventCancellationMailToUser($user, $event): void
    {
        $this->emailManager->sendEmail($user->getEmail(), $this->translator->trans('event.cancellation.subject', [], 'emails'), 'event_cancellation', ['event' => $event]);
    }

    // Send an event cancellation email to parents
    private function sendEventCancellationMailToParents($user, $event): void
    {
        foreach ($user->getParents() as $parent) {
            $this->emailManager->sendEmail($parent->getEmail(), $this->translator->trans('event.cancellation.subject', [], 'emails'), 'event_cancellation', ['event' => $event]);
        }
    }
}
