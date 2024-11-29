<?php

namespace App\Controller;

use App\Entity\License;
use App\Form\LicenseType;
use App\Form\UploadLicenseType;
use App\Repository\LicenseRepository;
use App\Service\EmailManager;
use App\Service\FileUploader;
use App\Service\LicensePDFGenerator as ServiceLicensePDFGenerator;
use App\Service\ProfileChecker;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/license')]
class LicenseController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LicenseRepository $licenseRepository;
    private TranslatorInterface $translator;
    private ProfileChecker $profileChecker;

    public function __construct(EntityManagerInterface $entityManager, LicenseRepository $licenseRepository, TranslatorInterface $translator, ProfileChecker $profileChecker)
    {
        $this->entityManager = $entityManager;
        $this->licenseRepository = $licenseRepository;
        $this->translator = $translator;
        $this->profileChecker = $profileChecker;
    }

    // Display all licences for the user
    #[Route('/', name: 'app_license')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $user = $this->getUser();

        $currentYearActiveLicense = $this->licenseRepository->getCurrentYearActiveLicense($user);

        $currentYearPendingLicense = $this->licenseRepository->getCurrentYearPendingLicense($user);

        $pastYearsLicenses = $this->licenseRepository->getPastYearsLicenses($user);

        return $this->render('license/index.html.twig', [
            'currentYearActiveLicense' => $currentYearActiveLicense,
            'currentYearPendingLicense' => $currentYearPendingLicense,
            'pastYearsLicenses' => $pastYearsLicenses
        ]);
    }

    // Create a licence request
    #[Route('/create', name: 'app_license_create')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EmailManager $emailManager): Response
    {
        $user = $this->getUser();

        // Profile must be complete to ask a new license
        $errorMessage = $this->profileChecker->checkProfileCompletion($user);
        if ($errorMessage) {
            $this->addFlash('error', $errorMessage);
            return $this->redirectToRoute('app_profile_update');
        }

        // Only one license can be asked for the current year
        $currentLicense = $this->licenseRepository->getCurrentYearActiveLicense($user);
        $currentYearPendingLicense = $this->licenseRepository->getCurrentYearPendingLicense($user);

        if ($currentLicense || $currentYearPendingLicense) {
            $this->addFlash('error', $this->translator->trans('error.license.already_exist'));
            return $this->redirectToRoute('app_license');
        }

        $license = new License();
        $form = $this->createForm(LicenseType::class, $license);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Setup basic licence information
            $license->setSeason(date('Y'));
            $license->setStatus(License::ON_DEMAND);
            $timestamp = new DateTimeImmutable();
            $license->setUpdatedAt($timestamp);
            $license->setUser($user);
            $license->setUserLastUpdate($user);

            $this->entityManager->persist($license);
            $this->entityManager->flush();

            $emailManager->sendEmail(EmailManager::ADMIN_MAIL, 'Nouvelle demande de licence', 'new_license', ['user' => $user]);

            return $this->redirectToRoute('app_license');
        }

        return $this->render('license/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Generate the licence request document and download it
    #[Route('/generate/{licenseId}', name: 'app_license_generate')]
    #[IsGranted('ROLE_USER')]
    public function generate(Request $request, ServiceLicensePDFGenerator $pdfGenerator): Response
    {
        try {
            // We generate the document each time this route is called because the profile data can be updated between steps
            $license = $this->findLicense($request);

            $this->restrictAccessIfUserIsNotOwnerOf($license);

            // Generate and save the document on the server
            $ouputFileName = $pdfGenerator->generate($license);

            // Add the filename to the demand_file field in the License entity
            $license->setDemandFile($ouputFileName);

            // Change the License status to License::DOC_DOWNLOADED
            $license->setStatus(License::DOC_DOWNLOADED);

            // Persist and flush the changes to the database
            $this->entityManager->persist($license);
            $this->entityManager->flush();

            $demandFileName = $license->getDemandFile();

            // Redirect to a page to download the file
            return $this->render('license/download.html.twig', [
                'file_name' => $demandFileName,
                'licenseId' => $license->getId(),
            ]);
        } catch (EntityNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_license');
        } catch (FileException $e) {
            $this->addFlash('error', $this->translator->trans('error.license.file_not_found', ['message' => $e->getMessage()]));
            return $this->redirectToRoute('app_license');
        }
    }

    // Download licence request
    #[Route('/download/{licenseId}/{type}', name: 'app_license_download')]
    public function download(Request $request, string $licenseId)
    {
        try {
            $license = $this->findLicense($request);

            if (!$this->isGranted('ROLE_ADMIN') && $this->getUser() !== $license->getUser()) {
                $this->addFlash('error', 'Vous n\'avez pas accès à ce fichier');
                return $this->redirectToRoute('app_license');
            }

            $typeDoc = $request->get('type');
            if ($typeDoc == 'demand') {
                $absolutePath = $this->getParameter('downloaded_licenses_directory') . $license->getDemandFile();
            } else if ($typeDoc == 'upload') {
                $absolutePath = $this->getParameter('uploaded_licenses_directory') . $license->getUploadedFile();
            }

            $response = new BinaryFileResponse($absolutePath);

            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $license->getDemandFile()
            );

            $response->headers->set('Content-Disposition', $disposition);

            return $response;
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_license');
        }
    }

    // Form to upload the filled licence request
    #[Route('/upload/{licenseId}', name: 'app_license_upload')]
    #[IsGranted('ROLE_USER')]
    public function upload(Request $request, FileUploader $fileUploader, EmailManager $emailManager): Response
    {
        try {
            $license = $this->findLicense($request);

            $this->restrictAccessIfUserIsNotOwnerOf($license);

            $form = $this->createForm(UploadLicenseType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $uploadedFile = $form->get('uploaded_file')->getData();

                if ($uploadedFile) {
                    $uploadedFileName = $fileUploader->save($uploadedFile, 'uploaded_licenses_directory');
                    $license->setUploadedFile($uploadedFileName);
                    // Change License Status
                    $license->setStatus(License::DOC_RECEIVED);

                    $this->entityManager->persist($license);
                    $this->entityManager->flush();

                    // Send a mail to administrateur
                    // Always sent in French -- No need to translate this mail
                    $emailManager->sendEmail(EmailManager::ADMIN_MAIL, 'Licence à valider', 'license_to_validate', ['user' => $license->getUser()]);

                    $this->addFlash('success', $this->translator->trans('success.license.sent'));
                } else {
                    $this->addFlash('error', $this->translator->trans('error.license.no_file_uploaded'));
                }

                return $this->redirectToRoute('app_license');
            }

            return $this->render('license/upload.html.twig', [
                'form' => $form->createView(),
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_license');
        }
    }

    // TODO : Create a service with these private functions

    // Find a licence
    private function findLicense(Request $request): License
    {
        $licenseId = $request->get('licenseId');
        $license = $this->licenseRepository->find($licenseId);

        if (!$license) {
            throw new EntityNotFoundException($this->translator->trans('error.license.not_found'));
        }

        // Check if the user is authorized to access the license
        if (!$this->isGranted('ROLE_ADMIN') && $license->getUser() !== $this->getUser()) {
            throw new AccessDeniedException($this->translator->trans('error.license.not_authorized'));
        }

        return $license;
    }

    // Restrict access if user is not the owner of the license
    private function restrictAccessIfUserIsNotOwnerOf(License $license): ?Response
    {
        if ($this->getUser() !== $license->getUser()) {
            $this->addFlash('error', $this->translator->trans('error.access_denied'));

            return $this->redirectToRoute('app_license');
        }

        return null;
    }
}
