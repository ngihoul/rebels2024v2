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

class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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

    #[Route('/profile/{userId}', name: 'app_profile_user')]
    #[IsGranted('ROLE_ADMIN')]
    public function profileUser(Request $request, UserRepository $userRepository): Response
    {
        $userId = $request->get('userId');

        $user = $userRepository->find($userId);

        return $this->render('profile/index.html.twig', ['user' => $user]);
    }

    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function profile(): Response
    {
        $user = $this->getUser();

        return $this->render('profile/index.html.twig', ['user' => $user]);
    }

    #[Route('/edit-profile', name: 'app_edit_profile')]
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

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/update.html.twig', [
            'form' => $form->createView(),
            'image' => $user->getProfilePicture()
        ]);
    }
}
