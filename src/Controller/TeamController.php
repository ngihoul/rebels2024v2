<?php

namespace App\Controller;

use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TeamController extends AbstractController
{
    #[Route('/teams', name: 'app_teams')]
    #[IsGranted('ROLE_USER')]
    public function index(TeamRepository $teamRepository): Response
    {
        $user = $this->getUser();
        $teams = $user->getTeams();

        return $this->render('teams/index.html.twig', [
            'teams' => $teams
        ]);
    }
}
