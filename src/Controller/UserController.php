<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\UserType;
use App\Repository\EventRepository;
use App\Repository\LicenseRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\ProfilePictureManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private LoggerInterface $logger;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    // Homepage for authenticated user. If not authenticated, redirected to login page
    #[Route(path: "/", name: "app_home")]
    #[IsGranted("ROLE_USER")]
    public function index(LicenseRepository $licenseRepository, EventRepository $eventRepository, MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();

        // License summary
        $isProfileCompleted = $user->isProfileComplete();

        $pendingLicenses = $licenseRepository->getCurrentYearPendingLicenses($user);
        $pendingLicense = $pendingLicenses ? $pendingLicenses[0] : null;
        $activeLicenses = $licenseRepository->getCurrentYearActiveLicense($user);

        // Pending events
        $pendingEvents = $eventRepository->findPendingEventsForThisUser($user);

        // Events summary
        $futureEvents = $eventRepository->findFutureEventsForThisUser($user);

        // Messages summary
        $messages = $messageRepository->findThreeLatest($user);

        $isUnreadMessage = $messageRepository->countUnreadMessagesForThisUser($user);

        return $this->render('home/index.html.twig', [
            'isProfileComplete' => $isProfileCompleted,
            'pendingLicense' => $pendingLicense,
            'activeLicenses' => $activeLicenses,
            'futureEvents' => $futureEvents,
            'messages' => $messages,
            'isUnreadMessage' => $isUnreadMessage,
            'pendingEvents' => $pendingEvents
        ]);
    }

    // Update his own profile
    #[Route('/profile/update', name: 'app_profile_update')]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, ProfilePictureManager $profilePictureManager): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);

        // Handle form
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $profilePictureManager->handleProfilePicture($form, $user);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('success.profile_updated'));
            return $this->redirectToRoute('app_profile', ['userId' => $user->getId()]);
        }

        return $this->render('profile/form.html.twig', [
            'form' => $form->createView(),
            'image' => $user->getProfilePicture()
        ]);
    }

    // Display user's profile for COACH/ADMIN
    #[Route('/profile/{userId}', name: 'app_profile_user')]
    #[IsGranted('ROLE_COACH')]
    public function otherProfile($userId, Request $request): Response
    {
        try {
            $userId = $request->get('userId');
            $user = $this->userRepository->find($userId);

            if (!$user) {
                throw new EntityNotFoundException($this->translator->trans('error.profile_not_found'));
            }

            $currentUser = $this->getUser();

            // Only coaches or admins can view a profile other than their own 
            if ($user !== $currentUser && (!$this->isGranted("ROLE_COACH"))) {
                $this->addFlash('error', $this->translator->trans('error.profile_not_found'));
                return $this->redirectToRoute('app_home');
            }

            // Generate title : My profile or Name's profile
            $pageTitle = ($user === $currentUser) ? $this->translator->trans('profile.my_profile') : $this->translator->trans('profile.profile_of') . $user->getFirstname() . ' ' . $user->getLastname();

            return $this->render('profile/index.html.twig', [
                'user' => $user,
                'pageTitle' => $pageTitle
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }

    // Display his own profile for USER
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function profile(Request $request): Response
    {
        try {
            $user = $this->userRepository->find($this->getUser());

            if (!$user) {
                throw new EntityNotFoundException($this->translator->trans('error.profile_not_found'));
            }

            // Generate title : My profile or Name's profile
            $pageTitle = $this->translator->trans('profile.my_profile');

            return $this->render('profile/index.html.twig', [
                'user' => $user,
                'pageTitle' => $pageTitle
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }

    // Display users list
    #[Route('/members/{page<\d+>?1}', name: 'app_members')]
    #[IsGranted('ROLE_ADMIN')]
    public function members(Request $request, PaginatorInterface $paginator): Response
    {
        $MEMBERS_PER_PAGE = 25;
        $ORDER_BY_DEFAULT = 'u.lastname';
        $ORDER_DIRECTION_DEFAULT = 'ASC';

        // Simple Search
        $searchQuery = $request->get('q');
        // Advanced Search
        $searchParams = [
            'firstname' => $request->get('firstname'),
            'lastname' => $request->get('lastname'),
            'gender' => $request->get('gender'),
            'ageMin' => $request->get('ageMin'),
            'ageMax' => $request->get('ageMax'),
            'licenseStatus' => $request->get('licenseStatus'),
        ];
        // OrderBY definition
        $orderBy = $request->query->get('order', $ORDER_BY_DEFAULT);
        $orderDirection = $request->query->get('dir', $ORDER_DIRECTION_DEFAULT);

        // Fetch Data
        if (array_filter($searchParams) !== []) {
            $members = $this->userRepository->advancedSearch($searchParams, $orderBy, $orderDirection);
        } else {
            $members = $this->userRepository->findAllWithCurrentYearLicense($searchQuery, $orderBy, $orderDirection);
        }

        $countMembers = count($members);
        // Pagination 
        $page = (int) $request->get('page');
        $membersPaginated = $paginator->paginate(
            $members,
            $page,
            $MEMBERS_PER_PAGE
        );

        return $this->render('member/list.html.twig', [
            'members' => $membersPaginated,
            'count' => $countMembers
        ]);
    }

    #[Route('/switch-user/{userId}', name: 'app_switch_user')]
    #[IsGranted('ROLE_USER')]
    public function impersonate($userId, AuthorizationCheckerInterface $authChecker, Request $request): RedirectResponse
    {
        $parent = $this->getUser();

        if (!$parent) {
            throw $this->createNotFoundException($this->translator->trans('error.user_not_found'));
        }

        $child = $this->userRepository->find($userId);

        if (!$child) {
            throw $this->createNotFoundException($this->translator->trans('error.user_not_found'));
        }

        $this->logger->info('Rôles de l\'utilisateur courant:', ['roles' => $parent->getRoles()]);

        if (!$authChecker->isGranted('SWITCH', $child)) {
            throw $this->createAccessDeniedException($this->translator->trans('error.access_denied'));
        }

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

        $this->logger->info('Rôles de l\'utilisateur impersonné:', ['roles' => $child->getRoles()]);
        $this->logger->info('Token après impersonation:', ['token' => $impersonationToken]);


        $this->addFlash('warning', $this->translator->trans('warning.user_switched', ['firstname' => $child->getFirstname(), 'lastname' => $child->getLastname()]));

        return $this->redirectToRoute('app_home');
    }

    #[Route('/exit-switch-user', name: 'app_exit_switch_user')]
    public function unimpersonate(Request $request): RedirectResponse
    {
        $tokenStorage = $this->container->get('security.token_storage');

        if ($request->getSession()->get('_switch_user')) {
            $originalToken = unserialize($request->getSession()->get('_switch_user'));

            $request->getSession()->remove('_switch_user');
            $tokenStorage->setToken($originalToken);

            $this->addFlash('success', "Vous êtes de retour sur votre compte.");
        }

        return $this->redirectToRoute('app_home');
    }
}
