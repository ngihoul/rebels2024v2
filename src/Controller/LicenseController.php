<?php

namespace App\Controller;

use App\Entity\License;
use App\Form\LicenseType;
use App\Form\UploadLicenseType;
use App\Repository\LicenseRepository;
use App\Service\FileUploader;
use App\Service\LicensePDFGenerator as ServiceLicensePDFGenerator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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

        if (!$user->isProfileComplete()) {
            $this->addFlash('error', 'Votre profil est incomplet. Complétez d\'abord votre profil et ensuite demandez votre licence.');
            return $this->redirectToRoute('app_edit_profile');
        }

        try {
            // Tentative de recherche de la licence actuelle de l'utilisateur
            $currentLicense = $this->licenseRepository->getCurrentYearActiveLicense($user);

            if ($currentLicense) {
                $this->addFlash('error', 'Vous avez déjà une licence active pour cette année. Vous ne pouvez pas en demander une nouvelle.');
                return $this->redirectToRoute('app_licenses');
            }
        } catch (EntityNotFoundException $e) {
            // Gérer l'entité non trouvée (currentLicense)
        }

        $license = new License();
        $form = $this->createForm(LicenseType::class, $license);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $license->setSeason(date('Y'));
            $license->setStatus(License::ON_DEMAND);
            $timestamp = new DateTimeImmutable();
            $license->setCreatedAt($timestamp);
            $license->setUpdatedAt($timestamp);
            $license->setUser($user);
            $license->setUserLastUpdate($user);

            $this->em->persist($license);
            $this->em->flush();

            return $this->redirectToRoute('app_licenses');
        }

        return $this->render('license/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/download-license/{licenseId}', name: 'app_download_license')]
    #[IsGranted('ROLE_USER')]
    public function download(Request $request, ServiceLicensePDFGenerator $pdfGenerator): Response
    {
        $licenseId = $request->get('licenseId');
        try {
            $license = $this->licenseRepository->find($licenseId);
            if (!$license) {
                throw new EntityNotFoundException('License not found.');
            }

            // Generate and save the document on the server
            $ouputFileName = $pdfGenerator->generate($license);

            // Add the filename to the demand_file field in the License entity
            $license->setDemandFile($ouputFileName);

            // Change the License status to License::DOC_DOWNLOADED
            $license->setStatus(License::DOC_DOWNLOADED);

            $license->setUpdatedAt(new DateTimeImmutable());

            // Persist and flush the changes to the database
            $this->em->persist($license);
            $this->em->flush();

            $demandFileName = $license->getDemandFile();

            // Redirect to a page to download the file with a link to upload
            return $this->render('license/download.html.twig', [
                'file_name' => $demandFileName,
                'licenseId' => $license->getId(),
            ]);
        } catch (EntityNotFoundException $e) {
            $this->addFlash('error', 'La licence demandée n\'a pas été trouvée.');
            return $this->redirectToRoute('app_licenses');
        } catch (FileException $e) {
            $this->addFlash('error', 'Le fichier n\'a pas pu être généré car ' . $e->getMessage());
            return $this->redirectToRoute('app_licenses');
        }
    }

    #[Route('/upload-license/{licenseId}', name: 'app_upload_license')]
    #[IsGranted('ROLE_USER')]
    public function upload(Request $request, FileUploader $fileUploader): Response
    {
        $licenseId = $request->get('licenseId');

        // Find license in DB
        try {
            $license = $this->licenseRepository->find($licenseId);
            if (!$license) {
                throw new EntityNotFoundException('License not found.');
            }
        } catch (EntityNotFoundException $e) {
            $this->addFlash('error', 'La licence demandée n\'a pas été trouvée.');
            return $this->redirectToRoute('app_licenses');
        }

        $form = $this->createForm(UploadLicenseType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('uploaded_file')->getData();

            if ($uploadedFile) {
                try {
                    $uploadedFileName = $fileUploader->save($uploadedFile, 'uploaded_licenses_directory');
                    $license->setUploadedFile($uploadedFileName);
                    // Change License Status
                    $license->setStatus(License::DOC_RECEIVED);

                    $license->setUpdatedAt(new DateTimeImmutable());
                    // Save data in DB
                    $this->em->persist($license);
                    $this->em->flush();

                    // Send a mail to administrateur

                    $this->addFlash('success', 'Votre licence a été envoyée avec succès. Un administrateur vérifiera le document et validera votre demande.');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Le fichier n\'a pas pu être enregistré car ' . $e->getMessage());
                }
            } else {
                $this->addFlash('error', 'Aucun fichier n\'a été téléchargé.');
            }

            return $this->redirectToRoute('app_licenses');
        }

        return $this->render('license/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
