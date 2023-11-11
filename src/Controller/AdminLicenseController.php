<?php

namespace App\Controller;

use App\Repository\LicenseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminLicenseController extends AbstractController
{
    private LicenseRepository $licenseRepository;

    public function __construct(LicenseRepository $licenseRepository)
    {
        $this->licenseRepository = $licenseRepository;
    }

    #[Route('/admin/licenses', name: 'app_admin_license_to_validate')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $licenses = $this->licenseRepository->findAllLicensesToValidate();

        return $this->render('admin/license/list.html.twig', [
            'licenses' => $licenses,
        ]);
    }

    #[Route('/admin/validate-license/{licenseId}', name: 'app_admin_validate_license')]
    #[IsGranted('ROLE_ADMIN')]
    public function validate(Request $request): Response
    {
        $licenseId = $request->get('licenseId');
        $license = $this->licenseRepository->find($licenseId);

        dd($license);
    }
}
