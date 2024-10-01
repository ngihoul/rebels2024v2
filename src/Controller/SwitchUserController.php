<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SwitchUserController extends AbstractController
{
    private UserRepository $userRepository;
    private TranslatorInterface $translator;

    public function __construct(UserRepository $userRepository, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->translator = $translator;
    }

    #[Route('/switch-user/{userId}', name: 'app_switch_user')]
    #[IsGranted('ROLE_USER')]
    public function impersonate($userId, AuthorizationCheckerInterface $authChecker, Request $request): RedirectResponse
    {
        $parent = $this->getUser();
        $this->ensureUserExists($parent);

        $child = $this->userRepository->find($userId);
        $this->ensureUserExists($child);

        $this->ensureAccessGranted($authChecker, $child);

        $this->switchUser($request, $child);

        return $this->redirectToRoute('app_home');
    }

    private function ensureUserExists($user): void
    {
        if (!$user) {
            throw $this->createNotFoundException($this->translator->trans('error.user_not_found'));
        }
    }

    private function ensureAccessGranted(AuthorizationCheckerInterface $authChecker, $child): void
    {
        if (!$authChecker->isGranted('SWITCH', $child)) {
            throw $this->createAccessDeniedException($this->translator->trans('error.access_denied'));
        }
    }

    private function switchUser(Request $request, $child): void
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $originalToken = $tokenStorage->getToken();

        if (!$request->getSession()->get('_switch_user')) {
            $request->getSession()->set('_switch_user', serialize($originalToken));
        }

        $impersonationToken = new UsernamePasswordToken(
            $child,
            "main",
            $child->getRoles()
        );

        $tokenStorage->setToken($impersonationToken);

        $this->addFlash('warning', $this->translator->trans('warning.user_switched', [
            'firstname' => $child->getFirstname(),
            'lastname' => $child->getLastname()
        ]));
    }

    #[Route('/exit-switch-user', name: 'app_exit_switch_user')]
    public function unimpersonate(Request $request): RedirectResponse
    {
        $tokenStorage = $this->container->get('security.token_storage');

        if ($request->getSession()->get('_switch_user')) {
            $originalToken = unserialize($request->getSession()->get('_switch_user'));

            $request->getSession()->remove('_switch_user');
            $tokenStorage->setToken($originalToken);

            $user = $originalToken->getUser();

            $this->addFlash('success', $this->translator->trans('warning.user_switched', [
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname()
            ]));
        }

        return $this->redirectToRoute('app_home');
    }
}
