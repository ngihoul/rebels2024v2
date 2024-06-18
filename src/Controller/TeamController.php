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

    // Display teams list
    #[Route('/teams', name: 'app_teams')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $user = $this->getUser();
        $teamsAsPlayer = $user->getTeams();
        $teamsAsCoach = $user->getCoachOf();
        $allTeams = [];

        // Only ADMIN can see all the teams of the club
        if ($this->isGranted('ROLE_ADMIN')) {
            $allTeams = $this->teamRepository->findAll();
        }

        return $this->render('team/index.html.twig', [
            'teamsAsPlayer' => $teamsAsPlayer,
            'teamsAsCoach' => $teamsAsCoach,
            'allTeams' => $allTeams
        ]);
    }

    // Create a new team
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

                // Save logo on server & filename in DB
                if ($logo) {
                    $logoFileName = $fileUploader->save($logo, 'logos_directory');
                    $team->setLogo($logoFileName);
                }

                // Assign ROLE_COACH to coach and assistant
                $coach = $form->get('coach')->getData();
                $assistant = $form->get('assistant')->getData();

                // If coach or assistant is ADMIN, do not change his role
                $this->updateRoles($coach, 'ROLE_COACH');
                $this->updateRoles($assistant, 'ROLE_COACH');

                $this->entityManager->persist($team);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('success.team_created'));
                return $this->redirectToRoute('app_teams');
            }

            return $this->render('team/form.html.twig', [
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

    // Update an existing team
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

            $oldCoach = $team->getCoach();
            $oldAssistant = $team->getAssistant();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $logo = $form->get('logo')->getData();

                // Save logo on server & filename in DB
                if ($logo) {
                    $logoFileName = $fileUploader->save($logo, 'logos_directory');
                    $team->setLogo($logoFileName);
                }

                // Modify ROLE
                $newCoach = $form->get('coach')->getData();
                $newAssistant = $form->get('assistant')->getData();

                // If coach or assistant are different than the previous one AND coach or assistant is ADMIN, do not change his role
                if ($oldCoach !== $newCoach) {
                    $this->updateRoles($oldCoach, 'ROLE_USER');
                    $this->updateRoles($newCoach, 'ROLE_COACH');
                }

                if ($oldAssistant !== $newAssistant) {
                    $this->updateRoles($oldAssistant, 'ROLE_USER');
                    $this->updateRoles($newAssistant, 'ROLE_COACH');
                }

                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('success.team.update'));
                return $this->redirectToRoute('app_teams');
            }

            return $this->render('team/form.html.twig', [
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

    // Display team detail and add a new player to the team
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
                    // Find player to add
                    $user = $this->userRepository->findOneBy(['id' => $data['user']]);

                    // Add player to the team
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

            return $this->render('team/detail.html.twig', [
                'team' => $team,
                'players' => $players,
                'form' => $form
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_teams');
        }
    }

    // Remove player from the team
    #[Route('/team/{teamId}/remove/{userId}', name: 'app_team_remove')]
    #[IsGranted('ROLE_COACH')]
    public function removeUserFromTeam(Request $request): Response
    {
        $teamId = $request->get('teamId');
        $userId = $request->get('userId');
        $token = $request->query->get('_token');

        if (!$this->isCsrfTokenValid('delete_player' . $userId, $token)) {
            $this->addFlash('error', $this->translator->trans('error.invalid_csrf_token'));
            return $this->redirectToRoute('app_team_detail', ['teamId' => $teamId]);
        }

        try {
            $team = $this->findTeam($teamId);

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


    // Delete a team
    #[Route('/team/{teamId}/delete', name: 'app_team_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request): Response
    {
        $teamId = $request->get('teamId');
        $team = $this->findTeam($teamId);

        try {
            // Delete Roles for coach & assistant
            // Only if coach or assistant are not ADMIN
            $this->updateRoles($team->getCoach(), 'ROLE_USER');
            $this->updateRoles($team->getAssistant(), 'ROLE_USER');

            // Delete the team from db
            $this->entityManager->remove($team);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('success.team.delete'));
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        $route = $request->headers->get('referer');
        return $this->redirect($route);
    }

    // Find a team
    private function findTeam(string $teamId): Team
    {
        $team = $this->teamRepository->find($teamId);

        if (!$team) {
            throw new EntityNotFoundException($this->translator->trans('error.team.not_found'));
        }

        return $team;
    }

    //Update role only if user is not admin
    private function updateRoles($person, $role)
    {
        if (!in_array('ROLE_ADMIN', $person->getRoles())) {
            $person->setRoles([$role]);
        }
    }
}
