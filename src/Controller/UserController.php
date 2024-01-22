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
                throw new EntityNotFoundException('Membre non trouvé.');
            }

            $currentUser = $this->getUser();

            if ($user !== $currentUser && (!$this->isGranted("ROLE_COACH") || !$this->isGranted('ROLE_ADMIN'))) {
                $this->addFlash('error', 'Le profil demandé n\'a pas été trouvé.');
                return $this->redirectToRoute('app_home');
            }

            $pageTitle = ($user === $currentUser) ? 'Mon profil' : 'Profil de ' . $user->getFirstname() . ' ' . $user->getLastname();

            return $this->render('profile/index.html.twig', [
                'user' => $user,
                'pageTitle' => $pageTitle
            ]);
        } catch (EntityNotFoundException $e) {
            $this->addFlash('error', 'Le profil demandé n\'a pas été trouvé.');
            return $this->redirectToRoute('app_home');
        }
    }
}
