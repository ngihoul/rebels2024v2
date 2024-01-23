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
    #[Route('/login', name: 'app_login', locale: 'fr')]
    public function index(AuthenticationUtils $authenticationUtils, TranslatorInterface $translator, Request $request): Response
    {
        // Denied access if user is already logged in.
        if ($this->getUser()) {
            $this->addFlash('error', $translator->trans('error.already_logged'));
            return $this->redirectToRoute('app_home', ['_locale' => $request->getLocale()]);
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }
}
