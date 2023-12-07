<?php

namespace App\Controller;

use App\Form\AddUserToTeam;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TeamController extends AbstractController
{
    private TeamRepository $teamRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(TeamRepository $teamRepository, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->teamRepository = $teamRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/teams', name: 'app_teams')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $user = $this->getUser();
        $teamsAsPlayer = $user->getTeams();
        $teamsAsCoach = $user->getCoachOf();

        return $this->render('teams/index.html.twig', [
            'teamsAsPlayer' => $teamsAsPlayer,
            'teamsAsCoach' => $teamsAsCoach,
        ]);
    }

    #[Route('/team/{teamId}', name: 'app_team_detail')]
    #[IsGranted('ROLE_USER')]
    public function detail(Request $request): Response
    {
        try {
            $teamId = $request->get('teamId');
            $team = $this->teamRepository->find($teamId);

            $form = $this->createForm(AddUserToTeam::class, null, [
                'team' => $team,
            ]);
            // Avoid testing if form si submitted for the first loading
            if ($request->isMethod('POST')) {
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $data = $form->getData();
                    $user = $this->userRepository->findOneBy(['id' => $data['user']]);

                    $team->addPlayer($user);

                    $this->entityManager->persist($team);
                    $this->entityManager->flush();

                    $this->addFlash('success', 'Joueur ajouté à l\'équipe !');
                } else {
                    $this->addFlash('error', 'Une erreur est survenue: le joueur n\'a pas pu être ajouté');
                }
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
        }

        return $this->render('teams/detail.html.twig', [
            'team' => $team,
            'form' => $form
        ]);
    }

    #[Route('/team/{teamId}/remove-user/{userId}', name: 'app_remove_user_from_team')]
    #[IsGranted('ROLE_USER')]
    public function removeUserFromTeam(int $teamId, int $userId): Response
    {
        try {
            $team = $this->teamRepository->find($teamId);
            $user = $this->userRepository->find($userId);

            if (!$team || !$user) {
                throw $this->createNotFoundException('Equipe ou Utilisateur non trouvé.');
            }

            $team->removePlayer($user);
            $this->entityManager->persist($team);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le joueur a été retiré de l\'équipe');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_team_detail', ['teamId' => $team->getId()]);
    }
}
