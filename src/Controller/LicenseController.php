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
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/license')]
class LicenseController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LicenseRepository $licenseRepository;

    public function __construct(EntityManagerInterface $entityManager, LicenseRepository $licenseRepository)
    {
        $this->entityManager = $entityManager;
        $this->licenseRepository = $licenseRepository;
    }

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

    #[Route('/create', name: 'app_license_create')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): Response
    {
        $user = $this->getUser();

        // Profile must be complete to ask a new license
        if (!$user->isProfileComplete()) {
            $this->addFlash('error', 'ton profil est incomplet. Complète d\'abord ton profil et ensuite demande une licence.');
            return $this->redirectToRoute('app_profile_update');
        }

        // Only one license can be asked for the current year
        $currentLicense = $this->licenseRepository->getCurrentYearActiveLicense($user);

        if ($currentLicense) {
            $this->addFlash('error', 'Tu as déjà une licence active pour cette année. tu ne peux pas en demander une nouvelle.');
            return $this->redirectToRoute('app_license');
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

            $this->entityManager->persist($license);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_license');
        }

        return $this->render('license/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/download/{licenseId}', name: 'app_license_download')]
    #[IsGranted('ROLE_USER')]
    public function download(Request $request, ServiceLicensePDFGenerator $pdfGenerator): Response
    {
        $licenseId = $request->get('licenseId');

        try {
            $license = $this->licenseRepository->find($licenseId);
            // Access only if license exist or User is the owner of the license
            if (!$license || $license->getUser() !== $this->getUser()) {
                throw new EntityNotFoundException('License non trouvé.');
            }

            // Generate and save the document on the server
            $ouputFileName = $pdfGenerator->generate($license);

            // Add the filename to the demand_file field in the License entity
            $license->setDemandFile($ouputFileName);

            // Change the License status to License::DOC_DOWNLOADED
            $license->setStatus(License::DOC_DOWNLOADED);

            $license->setUpdatedAt(new DateTimeImmutable());

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
            $this->addFlash('error', 'La licence demandée n\'a pas été trouvée.');
            return $this->redirectToRoute('app_license');
        } catch (FileException $e) {
            $this->addFlash('error', 'Le fichier n\'a pas pu être généré car ' . $e->getMessage());
            return $this->redirectToRoute('app_license');
        }
    }

    #[Route('/upload/{licenseId}', name: 'app_license_upload')]
    #[IsGranted('ROLE_USER')]
    public function upload(Request $request, FileUploader $fileUploader, EmailManager $emailManager): Response
    {
        $licenseId = $request->get('licenseId');

        // Find license in DB
        try {
            $license = $this->licenseRepository->find($licenseId);
            // Access only if license exist or User is the owner of the license
            if (!$license || $license->getUser() !== $this->getUser()) {
                throw new EntityNotFoundException('License not found.');
            }
        } catch (EntityNotFoundException $e) {
            $this->addFlash('error', 'La licence demandée n\'a pas été trouvée.');
            return $this->redirectToRoute('app_license');
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
                    $this->entityManager->persist($license);
                    $this->entityManager->flush();

                    // Send a mail to administrateur
                    $emailManager->sendEmail(EmailManager::ADMIN_MAIL, 'Licence à valider', 'license_to_validate', ['user' => $license->getUser()]);

                    $this->addFlash('success', 'Ta licence a été envoyée avec succès. Un administrateur vérifiera le document et validera ta demande.');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Le fichier n\'a pas pu être enregistré car ' . $e->getMessage());
                }
            } else {
                $this->addFlash('error', 'Aucun fichier n\'a été téléchargé.');
            }

            return $this->redirectToRoute('app_license');
        }

        return $this->render('license/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/checkout/{licenseId}', name: 'app_license_checkout')]
    #[IsGranted('ROLE_USER')]
    public function checkout(Request $request)
    {
        $licenseId = $request->get('licenseId');

        // Find license in DB
        try {
            $license = $this->licenseRepository->find($licenseId);
            // Access only if license exist or User is the owner of the license
            if (!$license || $license->getUser() !== $this->getUser()) {
                throw new EntityNotFoundException('License not found.');
            }
        } catch (EntityNotFoundException $e) {
            $this->addFlash('error', 'La licence demandée n\'a pas été trouvée.');
            return $this->redirectToRoute('app_license');
        }

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
    }

    #[Route('/success-url/{licenseId}', name: 'app_success_payment')]
    public function successUrl(Request $request): Response
    {

        $licenseId = $request->get('licenseId');

        // Find license in DB
        try {
            $license = $this->licenseRepository->find($licenseId);
            // Access only if license exist or User is the owner of the license
            if (!$license || $license->getUser() !== $this->getUser()) {
                throw new EntityNotFoundException('License not found.');
            }
        } catch (EntityNotFoundException $e) {
            $this->addFlash('error', 'La licence demandée n\'a pas été trouvée.');
            return $this->redirectToRoute('app_license');
        }

        // Change status
        $license->setStatus(License::IN_ORDER);

        $license->setUpdatedAt(new DateTimeImmutable());
        // Save data in DB
        $this->entityManager->persist($license);
        $this->entityManager->flush();

        return $this->render('payment/success.html.twig', []);
    }

    #[Route('/cancel-url', name: 'app_cancel_payment')]
    public function cancelUrl(): Response
    {
        return $this->render('payment/cancel.html.twig', []);
    }
}
