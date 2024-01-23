<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\UserType;
use App\Repository\LicenseRepository;
use App\Repository\UserRepository;
use App\Service\ProfilePictureManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    #[Route(path: "/", name: "app_home")]
    #[IsGranted("ROLE_USER")]
    public function index(LicenseRepository $licenseRepository): Response
    {
        $user = $this->getUser();

        $isProfileCompleted = $user->isProfileComplete();

        $pendingLicenses = $licenseRepository->getCurrentYearPendingLicenses($user);

        $pendingLicense = $pendingLicenses ? $pendingLicenses[0] : null;

        $activeLicenses = $licenseRepository->getCurrentYearActiveLicense($user);

        return $this->render('home/index.html.twig', [
            'isProfileComplete' => $isProfileCompleted,
            'pendingLicense' => $pendingLicense,
            'activeLicenses' => $activeLicenses
        ]);
    }

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

        return $this->render('profile/update.html.twig', [
            'form' => $form->createView(),
            'image' => $user->getProfilePicture()
        ]);
    }

    #[Route('/profile/{userId}', name: 'app_profile')]
    public function profileUser(Request $request, UserRepository $userRepository): Response
    {
        try {
            $userId = $request->get('userId');
            $user = $userRepository->find($userId);

            if (!$user) {
                throw new EntityNotFoundException($this->translator->trans('error.profile_not_found'));
            }

            $currentUser = $this->getUser();

            if ($user !== $currentUser && (!$this->isGranted("ROLE_COACH") || !$this->isGranted('ROLE_ADMIN'))) {
                $this->addFlash('error', $this->translator->trans('error.profile_not_found'));
                return $this->redirectToRoute('app_home');
            }

            $pageTitle = ($user === $currentUser) ? $this->translator->trans('profile.my_profile') : $this->translator->trans('profile.profile_of') . $user->getFirstname() . ' ' . $user->getLastname();

            return $this->render('profile/index.html.twig', [
                'user' => $user,
                'pageTitle' => $pageTitle
            ]);
        } catch (EntityNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }
}
