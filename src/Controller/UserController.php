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
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
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

    // Display his own profile for USER and a user's profile for COACH/ADMIN
    #[Route('/profile/{userId}', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function profile(Request $request): Response
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
}
