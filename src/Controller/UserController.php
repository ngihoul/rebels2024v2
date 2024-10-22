<?php

namespace App\Controller;

use App\Entity\User;
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
use App\Service\EmailManager;
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
    private EmailManager $emailManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, TranslatorInterface $translator, EmailManager $emailManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->emailManager = $emailManager;
    }

    // Homepage for authenticated user. If not authenticated, redirected to login page
    #[Route(path: "/", name: "app_home")]
    #[IsGranted("ROLE_USER")]
    public function index(LicenseRepository $licenseRepository, EventRepository $eventRepository, MessageRepository $messageRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $missingFields = $user->isProfileComplete();

        $pendingLicenses = $licenseRepository->getCurrentYearPendingLicenses($user);
        $pendingLicense = $pendingLicenses ? $pendingLicenses[0] : null;

        return $this->render('home/index.html.twig', [
            'missingFields' => $missingFields,
            'pendingLicense' => $pendingLicense,
            'activeLicenses' => $licenseRepository->getCurrentYearActiveLicense($user),
            'futureEvents' => $eventRepository->findFutureEventsForThisUser($user),
            'messages' => $messageRepository->findThreeLatest($user),
            'isUnreadMessage' => $messageRepository->countUnreadMessagesForThisUser($user),
            'pendingEvents' => $eventRepository->findPendingEventsForThisUser($user)
        ]);
    }

    // Update his own profile
    #[Route('/profile/update', name: 'app_profile_update')]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, ProfilePictureManager $profilePictureManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $isUserChild = !$user->getParents()->isEmpty();

        $form = $this->createForm(UserType::class, $user, [
            'is_child' => $isUserChild,
            'roi' => !$user->isInternalRules(),
            'privacy_policy' => !$user->isPrivacyPolicy()
        ]);

        // Handle form
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $profilePictureManager->handleProfilePicture($form, $user);

            if ($user->canUseApp() && !$user->getCanUseAppFromDate()) {
                $user->setCanUseAppBy($user);
                $user->setCanUseAppFromDate(new \DateTimeImmutable());

                $this->emailManager->inviteChildToChoosePassword($user);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('success.profile_updated'));
            return $this->redirectToRoute('app_profile', ['userId' => $user->getId()]);
        }

        return $this->render('profile/form.html.twig', [
            'form' => $form->createView(),
            'image' => $user->getProfilePicture(),
            'isChild' => $isUserChild,
            'isROImissing' => !$user->isInternalRules(),
            'isPrivacyPolicyMissing' => !$user->isPrivacyPolicy()
        ]);
    }

    // Display user's profile for COACH/ADMIN
    #[Route('/profile/{userId}', name: 'app_profile_user')]
    // Role permission in the method
    public function otherProfile($userId, Request $request): Response
    {
        try {
            $userId = $request->get('userId');
            $user = $this->userRepository->find($userId);

            if (!$user) {
                throw new EntityNotFoundException($this->translator->trans('error.profile_not_found'));
            }

            /** @var User $currentUser */
            $currentUser = $this->getUser();

            // Only coaches or admins can view a profile other than their own AND Parents can see their children's profile
            if ($user !== $currentUser && !$this->isGranted("ROLE_COACH") && !$currentUser->getChildren()->contains($user)) {
                $this->addFlash('error', $this->translator->trans('error.profile_not_found'));
                return $this->redirectToRoute('app_home');
            }

            // Generate title : My profile or Name's profile
            $pageTitle = $this->translator->trans('profile.profile_of') . $user->getFirstname() . ' ' . $user->getLastname();
            $childrenTitle = $this->translator->trans('profile.children.title.of');

            return $this->render('profile/index.html.twig', [
                'user' => $user,
                'pageTitle' => $pageTitle,
                'childrenTitle' => $childrenTitle
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
            $childrenTitle = $this->translator->trans('profile.children.title.mines');

            return $this->render('profile/index.html.twig', [
                'user' => $user,
                'pageTitle' => $pageTitle,
                'childrenTitle' => $childrenTitle
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
