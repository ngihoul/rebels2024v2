<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\AddUserToTeam;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
        $allTeams = [];

        if ($this->isGranted('ROLE_ADMIN')) {
            $allTeams = $this->teamRepository->findAll();
        }

        return $this->render('teams/index.html.twig', [
            'teamsAsPlayer' => $teamsAsPlayer,
            'teamsAsCoach' => $teamsAsCoach,
            'allTeams' => $allTeams
        ]);
    }

    #[Route('/team/create', name: 'app_team_create')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, FileUploader $fileUploader): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);

        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $logo = $form->get('logo')->getData();

                if ($logo) {
                    $logoFileName = $fileUploader->save($logo, 'logos_directory');
                    $team->setLogo($logoFileName);
                }

                $this->entityManager->persist($team);
                $this->entityManager->flush();

                $this->addFlash('success', 'L\'équipe a été créée avec succès.');
                return $this->redirectToRoute('app_teams');
            }
        } catch (FileException $fileException) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'upload du logo : ' . $fileException->getMessage());
        } catch (Exception $exception) {
            $this->addFlash('error', 'Une erreur est survenue lors de la création de l\'équipe : ' . $exception->getMessage());
        }

        return $this->render('teams/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/team/{id}/update', name: 'app_team_update')]
    #[IsGranted('ROLE_ADMIN')]
    public function update(Team $team, Request $request, FileUploader $fileUploader): Response
    {
        $action = 'update';
        $logo = $team->getLogo();

        $form = $this->createForm(TeamType::class, $team);

        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $logo = $form->get('logo')->getData();

                if ($logo) {
                    $logoFileName = $fileUploader->save($logo, 'logos_directory');
                    $team->setLogo($logoFileName);
                }

                $this->entityManager->flush();

                $this->addFlash('success', 'L\'équipe a été mise à jour avec succès.');
                return $this->redirectToRoute('app_teams');
            }
        } catch (FileException $fileException) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'upload du logo : ' . $fileException->getMessage());
        } catch (Exception $exception) {
            $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de l\'équipe : ' . $exception->getMessage());
        }

        return $this->render('teams/form.html.twig', [
            'form' => $form->createView(),
            'action' => $action,
            'logo' => $logo
        ]);
    }

    #[Route('/team/{teamId}', name: 'app_team_detail')]
    #[IsGranted('ROLE_USER')]
    public function detail(Request $request): Response
    {
        try {
            $teamId = $request->get('teamId');
            $team = $this->teamRepository->find($teamId);

            if (!$team) {
                throw new EntityNotFoundException('L\'équipe n\'existe pas.');
            }

            $players = $team->getPlayers()->toArray();
            usort($players, function ($a, $b) {
                return strcmp($a->getLastName(), $b->getLastName());
            });

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
                    // Force to reload the player list
                    return $this->redirectToRoute('app_team_detail', ['teamId' => $team->getId()]);
                } else {
                    $this->addFlash('error', 'Une erreur est survenue: le joueur n\'a pas pu être ajouté');
                }
            }
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_teams');
        }

        return $this->render('teams/detail.html.twig', [
            'team' => $team,
            'players' => $players,
            'form' => $form
        ]);
    }

    #[Route('/team/{teamId}/remove/{userId}', name: 'app_team_remove')]
    #[IsGranted('ROLE_COACH')]
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
