<?php

namespace App\Controller;

use App\Repository\LicenseRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LicenseController extends AbstractController
{
    #[Route('/licenses', name: 'app_licenses')]
    #[IsGranted('ROLE_USER')]
    public function index(UserRepository $userRepository, LicenseRepository $licenseRepository): Response
    {
        $user = $this->getUser();

        $currentYearActiveLicenses = $licenseRepository->getCurrentYearActiveLicense($user);

        $currentYearPendingLicenses = $licenseRepository->getCurrentYearPendingLicenses($user);

        $pastYearsLicenses = $licenseRepository->getPastYearsLicenses($user);

        return $this->render('license/index.html.twig', [
            'currentYearActiveLicenses' => $currentYearActiveLicenses,
            'currentYearPendingLicenses' => $currentYearPendingLicenses,
            'pastYearsLicenses' => $pastYearsLicenses
        ]);
    }
}
