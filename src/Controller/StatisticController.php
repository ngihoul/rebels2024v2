<?php

namespace App\Controller;

use App\Repository\EventCategoryRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/statistics')]
#[IsGranted('ROLE_USER')]
class StatisticController extends AbstractController
{
    private EventRepository $eventRepository;
    private EventCategoryRepository $eventCategoryRepository;

    public function __construct(EventRepository $eventRepository, EventCategoryRepository $eventCategoryRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->eventCategoryRepository = $eventCategoryRepository;
    }

    // Display the basic page for Statistics
    // Only years with data are displayed in <select> tags
    #[Route('/', name: 'app_statistics')]
    public function index(): Response
    {
        $user = $this->getUser();
        $training = 'Entrainement';
        $game = 'Match';

        $trainingCategory = $this->eventCategoryRepository->findOneBy(['name' => $training]);
        // Fetch years whith data to populate <select>
        $yearsForTrainingStats = $this->eventRepository->findYearsWithAttendeesForUser($user, $trainingCategory);

        $gameCategory = $this->eventCategoryRepository->findOneBy(['name' => $game]);
        // Fetch years whith data to populate <select>
        $yearsForGameStats = $this->eventRepository->findYearsWithAttendeesForUser($user, $gameCategory);

        return $this->render('statistic/index.html.twig', [
            'yearsForTrainingStats' => $yearsForTrainingStats,
            'yearsForGameStats' => $yearsForGameStats
        ]);
    }

    // API to fetch data from user selection
    #[Route('/api/{category}/{year}', name: 'api_stats')]
    public function getData(TranslatorInterface $translator, int $year, string $category)
    {
        // Currently, only "Training" or "Game" are accepted as category
        if (!in_array($category, ['training', 'game'])) {
            return new JsonResponse(['error' => 'Invalid category. It must be "training" or "game".'], Response::HTTP_NOT_FOUND);
        }

        $categoryName = ($category === 'training') ? 'Entrainement' : 'Match';
        $category = $this->eventCategoryRepository->findOneBy(['name' => $categoryName]);

        $user = $this->getUser();

        $data = $this->eventRepository->countUserResponses($user, $year, $category);
        $total = $this->eventRepository->countTotalEvent($user, $year, $category);

        $result = [
            // Presence
            $translator->trans('stat.label.presence') => $data['presence'],
            // Absence
            $translator->trans('stat.label.absence') => $data['absence'],
            // No reply
            $translator->trans('stat.label.no_reply') => $total - ($data['presence'] + $data['absence']),
        ];

        return new JsonResponse($result, 200);
    }
}
