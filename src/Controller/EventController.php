<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/agenda', name: 'app_agenda')]
    public function index(): Response
    {
        return $this->render('agenda/index.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }

    #[Route('/create-event', name: 'app_create_event')]
    public function createEvent(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $event = new Event();

            $form = $this->createForm(EventType::class, $event);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $startDate = $form->get('date')->getData();
                $endDate = $form->get('end_date')->getData();
                $frequency = $form->get('frequency')->getData();

                $entityManager->beginTransaction();

                if ($endDate === null) {
                    $entityManager->persist($event);
                } else {
                    $currentDate = $startDate;
                    while ($currentDate <= $endDate) {
                        $newEvent = clone $event;
                        $newEvent->setDate($currentDate);

                        $entityManager->persist($newEvent);

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

                $entityManager->flush();
                $entityManager->commit();

                $this->addFlash('success', 'L\'évènement a bien été créé.');
                return $this->redirectToRoute('app_agenda');
            }
        } catch (\Exception $e) {
            $entityManager->rollback();

            $this->addFlash('error', 'Une erreur s\'est produite lors de la création de l\'événement.');
            return $this->redirectToRoute('app_agenda');
        }

        return $this->render('agenda/event_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
