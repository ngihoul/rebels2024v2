<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginController extends AbstractController
{
    private SessionInterface $session;

    public function __construct(RequestStack $requestStack,)
    {
        $this->session = $requestStack->getSession();
    }

    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils, TranslatorInterface $translator, Request $request, SessionInterface $session): Response
    {
        $user = $this->getUser();

        // Denied access if user is already logged in.
        if ($user) {
            $this->addFlash('error', $translator->trans('error.already_logged'));
            return $this->redirectToRoute('app_home', ['_locale' => $request->getLocale()]);
        }

        // Clean session
        $this->session->remove('step');
        $this->session->remove('user_choice');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Assuming the user is authenticated here, store the user ID in the session
        if ($user) {
            $session->set('activeUser', $user->getId());
        }

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }
}
