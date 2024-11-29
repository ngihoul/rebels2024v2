<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils, TranslatorInterface $translator, Request $request): Response
    {

        $user = $this->getUser();

        if ($user) {
            $this->addFlash('error', $translator->trans('error.already_logged'));
            return $this->redirectToRoute('app_home', ['_locale' => $request->getLocale()]);
        }

        $session = $request->getSession();

        $session->remove('step');
        $session->remove('user_choice');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }
}
