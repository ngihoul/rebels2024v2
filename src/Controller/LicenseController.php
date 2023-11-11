<?php

namespace App\Controller;

use App\Entity\License;
use App\Form\LicenseType;
use App\Repository\LicenseRepository;
use App\Service\LicensePDFGenerator as ServiceLicensePDFGenerator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LicenseController extends AbstractController
{
    private EntityManagerInterface $em;
    private LicenseRepository $licenseRepository;

    public function __construct(EntityManagerInterface $entityManager, LicenseRepository $licenseRepository)
    {
        $this->em = $entityManager;
        $this->licenseRepository = $licenseRepository;
    }

    #[Route('/licenses', name: 'app_licenses')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $user = $this->getUser();

        $currentYearActiveLicenses = $this->licenseRepository->getCurrentYearActiveLicense($user);

        $currentYearPendingLicenses = $this->licenseRepository->getCurrentYearPendingLicenses($user);

        $pastYearsLicenses = $this->licenseRepository->getPastYearsLicenses($user);

        return $this->render('license/index.html.twig', [
            'currentYearActiveLicenses' => $currentYearActiveLicenses,
            'currentYearPendingLicenses' => $currentYearPendingLicenses,
            'pastYearsLicenses' => $pastYearsLicenses
        ]);
    }

    #[Route('/add-license', name: 'app_add_license')]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        $user = $this->getUser();

        // Check if profile user is complete 
        if (!$user->isProfileComplete()) {
            $this->addFlash('error', 'Votre profil est incomplet. Complétez d\'abord votre profil et ensuite demandez votre license.');

            return $this->redirectToRoute('app_edit_profile');
        }

        // Check if User has already a License for this year
        $currentYearActiveLicenses = $this->licenseRepository->getCurrentYearActiveLicense($user);
        $currentYearPendingLicenses = $this->licenseRepository->getCurrentYearPendingLicenses($user);

        if (count($currentYearActiveLicenses) > 0 || count($currentYearPendingLicenses) > 0) {
            $this->addFlash('error', 'Vous avez déjà une licence (en cours de validation ou validée) pour cette année. Vous ne pouvez donc pas en redemander une pour l\'instant.');

            return $this->redirectToRoute('app_licenses');
        }

        $license = new License();
        $form = $this->createForm(LicenseType::class, $license);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $license->setSeason(date('Y'));
            // Set status to On Demand
            $license->setStatus(License::ON_DEMAND);
            // Fill in timestamp
            $created_at = new DateTimeImmutable();
            $license->setCreatedAt($created_at);
            $license->setUpdatedAt($created_at);
            // Set UserId
            $license->setUser($this->getUser());
            $license->setUserLastUpdate($this->getUser());

            $this->em->persist($license);
            $this->em->flush();

            return $this->redirectToRoute('app_licenses');
        }

        return $this->render('license/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/download-license/{idLicense}', name: 'app_download_license')]
    #[IsGranted('ROLE_USER')]
    public function download(Request $request, ServiceLicensePDFGenerator $pdfGenerator): Response
    {
        $licenseId = $request->get('idLicense');
        $license = $this->licenseRepository->find($licenseId);

        // Generate and save document on server
        $ouputFileName = $pdfGenerator->generate($license);
        // Add filename in demand_file field in License DB
        $license->setDemandFile($ouputFileName);

        // Change License status to License::DOC_DOWNLOADED
        $license->setStatus(License::DOC_DOWNLOADED);
        // Save to DB
        $this->em->persist($license);
        $this->em->flush();

        $demandFileName = $license->getDemandFile();

        // Rediriger vers page pour télécharger avec lien vers upload
        return $this->render('license/download.html.twig', [
            'file_name' => $demandFileName,
        ]);
    }
}
