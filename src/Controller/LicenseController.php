<?php

namespace App\Controller;

use App\Entity\License;
use App\Form\LicenseType;
use App\Form\UploadLicenseType;
use App\Repository\LicenseRepository;
use App\Service\EmailManager;
use App\Service\FileUploader;
use App\Service\LicensePDFGenerator as ServiceLicensePDFGenerator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/license')]
class LicenseController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LicenseRepository $licenseRepository;
    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, LicenseRepository $licenseRepository, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->licenseRepository = $licenseRepository;
        $this->translator = $translator;
    }

    // Display all licences for the user
    #[Route('/', name: 'app_license')]
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

    // Create a licence request
    #[Route('/create', name: 'app_license_create')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EmailManager $emailManager): Response
    {
        $user = $this->getUser();

        // Profile must be complete to ask a new license
        if (!$user->isProfileComplete()) {
            $this->addFlash('error', $this->translator->trans('error.license.profile_incomplete'));
            return $this->redirectToRoute('app_profile_update');
        }

        // Only one license can be asked for the current year
        $currentLicense = $this->licenseRepository->getCurrentYearActiveLicense($user);
        $currentYearPendingLicenses = $this->licenseRepository->getCurrentYearPendingLicenses($user);

        if ($currentLicense || $currentYearPendingLicenses) {
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
    #[Route('/download/{licenseId}', name: 'app_license_download')]
    #[IsGranted('ROLE_USER')]
    public function download(Request $request, ServiceLicensePDFGenerator $pdfGenerator): Response
    {
        try {
            // We generate the document each time this route is called because the profile data can be updated between steps
            $licenseId = $request->get('licenseId');
            $license = $this->findLicense($licenseId);

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

    // Form to upload the filled licence request
    #[Route('/upload/{licenseId}', name: 'app_license_upload')]
    #[IsGranted('ROLE_USER')]
    public function upload(Request $request, FileUploader $fileUploader, EmailManager $emailManager): Response
    {
        try {
            $licenseId = $request->get('licenseId');
            $license = $this->findLicense($licenseId);

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

    // Route to pay the licence via Stripe
    #[Route('/checkout/{licenseId}', name: 'app_license_checkout')]
    #[IsGranted('ROLE_USER')]
    public function checkout(Request $request)
    {
        try {
            $licenseId = $request->get('licenseId');
            $license = $this->findLicense($licenseId);

            Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

            $checkout_session = Session::create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'License annuelle',
                        ],
                        'unit_amount' => $license->getPrice() * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('app_success_payment', ['licenseId' => $licenseId], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('app_cancel_payment', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            return $this->redirect($checkout_session->url, 303);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_license');
        }
    }

    // Stripe payment success
    #[Route('/success-url/{licenseId}', name: 'app_success_payment')]
    public function successUrl(Request $request): Response
    {
        // Find license in DB
        try {
            $licenseId = $request->get('licenseId');
            $license = $this->findLicense($licenseId);

            // Change status
            $license->setStatus(License::IN_ORDER);

            // Save data in DB
            $this->entityManager->persist($license);
            $this->entityManager->flush();

            return $this->render('payment/success.html.twig', []);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_license');
        }
    }

    // Stripe payement refused or cancelled
    #[Route('/cancel-url', name: 'app_cancel_payment')]
    public function cancelUrl(): Response
    {
        return $this->render('payment/cancel.html.twig', []);
    }

    // Find a licence
    private function findLicense(string $licenseId)
    {
        $license = $this->licenseRepository->find($licenseId);
        if (!$license || $license->getUser() !== $this->getUser()) {
            throw new EntityNotFoundException($this->translator->trans('error.license.not_found'));
        }

        return $license;
    }
}
