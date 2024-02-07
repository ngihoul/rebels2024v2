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
use Symfony\Contracts\Translation\TranslatorInterface;

class TeamController extends AbstractController
{
    private TeamRepository $teamRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(TeamRepository $teamRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->teamRepository = $teamRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
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

        try {
            $action = 'create';

            $team = new Team();
            $form = $this->createForm(TeamType::class, $team);

            $form->handleRequest($request);


            if ($form->isSubmitted() && $form->isValid()) {
                $logo = $form->get('logo')->getData();

                if ($logo) {
                    $logoFileName = $fileUploader->save($logo, 'logos_directory');
                    $team->setLogo($logoFileName);
                }

                $this->entityManager->persist($team);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('success.team_created'));
                return $this->redirectToRoute('app_teams');
            }

            return $this->render('teams/form.html.twig', [
                'form' => $form->createView(),
                'action' => $action
            ]);
        } catch (FileException $fileException) {
            $this->addFlash('error', $this->translator->trans('error.team.logo'));
            return $this->redirectToRoute('app_teams');
        } catch (Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
            return $this->redirectToRoute('app_teams');
        }
    }

    #[Route('/team/{teamId}/update', name: 'app_team_update')]
    #[IsGranted('ROLE_ADMIN')]
    public function update(Request $request, FileUploader $fileUploader): Response
    {

        try {
            $action = 'update';

            $teamId = $request->get('teamId');
            $team = $this->findTeam($teamId);

            $logo = $team->getLogo();

            $form = $this->createForm(TeamType::class, $team);

            $form->handleRequest($request);


            if ($form->isSubmitted() && $form->isValid()) {
                $logo = $form->get('logo')->getData();

                if ($logo) {
                    $logoFileName = $fileUploader->save($logo, 'logos_directory');
                    $team->setLogo($logoFileName);
                }

                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('success.team.update'));
                return $this->redirectToRoute('app_teams');
            }

            return $this->render('teams/form.html.twig', [
                'form' => $form->createView(),
                'action' => $action,
                'logo' => $logo
            ]);
        } catch (FileException $fileException) {
            $this->addFlash('error', $this->translator->trans('error.team.logo'));
            return $this->redirectToRoute('app_teams');
        } catch (Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
            return $this->redirectToRoute('app_teams');
        }
    }

    #[Route('/team/{teamId}', name: 'app_team_detail')]
    #[IsGranted('ROLE_USER')]
    public function detail(Request $request): Response
    {
        try {
            $teamId = $request->get('teamId');
            $team = $this->findTeam($teamId);

            // Sort players by lastname
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

                    $this->addFlash('success', $this->translator->trans('success.team.player_added', [
                        'firstname' => $user->getFirstname(),
                        'lastname' => $user->getLastname(),
                        'teamName' => $team->getName()
                    ]));
                    // Force to reload the player list
                    return $this->redirectToRoute('app_team_detail', ['teamId' => $team->getId()]);
                } else {
                    throw new Exception($this->translator->trans('error.team.player_added'));
                }
            }

            return $this->render('teams/detail.html.twig', [
                'team' => $team,
                'players' => $players,
                'form' => $form
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_teams');
        }
    }

    #[Route('/team/{teamId}/remove/{userId}', name: 'app_team_remove')]
    #[IsGranted('ROLE_COACH')]
    public function removeUserFromTeam(Request $request): Response
    {
        try {
            $teamId = $request->get('teamId');
            $team = $this->findTeam($teamId);

            $userId = $request->get('userId');
            $user = $this->userRepository->find($userId);
            if (!$user) {
                throw new EntityNotFoundException($this->translator->trans('error.team.user_not_found'));
            }

            $team->removePlayer($user);

            $this->entityManager->persist($team);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('success.team.player_remove', [
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'teamName' => $team->getName()
            ]));
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_team_detail', ['teamId' => $team->getId()]);
    }

    private function findTeam(string $teamId): Team
    {
        $team = $this->teamRepository->find($teamId);

        if (!$team) {
            throw new EntityNotFoundException($this->translator->trans('error.team.not_found'));
        }

        return $team;
    }
}
