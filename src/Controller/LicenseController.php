<?php

namespace App\Controller;

use App\Entity\License;
use App\Entity\Payment;
use App\Entity\PaymentOrder;
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
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
        $errorMessage = $this->profileChecker->checkProfileCompletion($user);
        if ($errorMessage) {
            $this->addFlash('error', $errorMessage);
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
    #[Route('/generate/{licenseId}', name: 'app_license_generate')]
    #[IsGranted('ROLE_USER')]
    public function generate(Request $request, ServiceLicensePDFGenerator $pdfGenerator): Response
    {
        try {
            // We generate the document each time this route is called because the profile data can be updated between steps
            $licenseId = $request->get('licenseId');
            $license = $this->findLicense($licenseId);

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
            $licenseId = $request->get('licenseId');
            $license = $this->findLicense($licenseId);

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
            $licenseId = $request->get('licenseId');
            $license = $this->findLicense($licenseId);

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

    // Route to pay the licence via Stripe
    #[Route('/checkout/{licenseId}', name: 'app_license_checkout')]
    #[IsGranted('ROLE_USER')]
    public function checkout(Request $request)
    {
        try {
            $licenseId = $request->get('licenseId');
            $license = $this->findLicense($licenseId);

            $this->restrictAccessIfUserIsNotOwnerOf($license);

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
    #[IsGranted('ROLE_USER')]
    public function successUrl(Request $request): Response
    {
        // Find license in DB
        try {
            // Begin SQL transaction
            $this->entityManager->beginTransaction();

            $licenseId = $request->get('licenseId');
            $license = $this->findLicense($licenseId);

            $this->restrictAccessIfUserIsNotOwnerOf($license);

            // Create Payment Object
            $payment = $this->createPayment($license, Payment::BY_STRIPE, Payment::STATUS_COMPLETED);
            $this->entityManager->persist($payment);

            // Create PaymentOrder
            $paymentOrder = $this->createPaymentOrder($payment, $license);
            $this->entityManager->persist($paymentOrder);

            // Update License status
            $license->setStatus(License::IN_ORDER);

            $this->entityManager->persist($license);

            // Commit transaction
            $this->entityManager->commit();

            // Save objects in DB
            $this->entityManager->flush();

            return $this->render('payment/success.html.twig', []);
        } catch (Exception $e) {
            // If error : rollback
            $this->entityManager->rollback();

            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_license');
        }
    }

    // Stripe payement refused or cancelled
    #[Route('/cancel-url', name: 'app_cancel_payment')]
    #[IsGranted('ROLE_USER')]
    public function cancelUrl(): Response
    {
        return $this->render('payment/cancel.html.twig', []);
    }

    // Create a payment order by bank transfer for a license.
    #[Route('/bank_transfer/create/{licenseId}', name: 'app_license_create_bank_transfer')]
    #[IsGranted('ROLE_USER')]
    public function createBankTransfer(Request $request): Response
    {
        $licenseId = $request->get('licenseId');
        $license = $this->findLicense($licenseId);

        $this->restrictAccessIfUserIsNotOwnerOf($license);

        // Create Payment Object
        $payment = $this->createPayment($license, Payment::BY_BANK_TRANSFER, Payment::STATUS_ACCEPTED);
        $this->entityManager->persist($payment);

        // Create PaymentOrder
        $paymentOrder = $this->createPaymentOrder($payment, $license, new \DateTimeImmutable('+1 month'));
        $this->entityManager->persist($paymentOrder);

        $this->entityManager->flush();

        return $this->redirectToRoute('app_license');
    }

    // Delete payment order by bank transfer if user wants to change method payment
    #[Route('/bank_transfer/delete/{licenseId}', name: 'app_license_delete_bank_transfer')]
    #[IsGranted('ROLE_USER')]
    public function deleteBankTransfer(Request $request): Response
    {
        $licenseId = $request->get('licenseId');
        $license = $this->findLicenseWithPayments($licenseId);

        $this->restrictAccessIfUserIsNotOwnerOf($license);

        // Delete PaymentOrder
        $payments = $license->getPayments();

        $paymentToDelete = $payments->filter(fn($p) => $p->getPaymentType() === Payment::BY_BANK_TRANSFER)->first();

        foreach ($paymentToDelete->getPaymentOrders() as $order) {
            $this->entityManager->remove($order);
        }

        // Delete payment
        $this->entityManager->remove($paymentToDelete);

        $this->entityManager->flush();

        return $this->redirectToRoute('app_license');
    }

    // Asking form for a customized payment plan
    #[Route('/payment_plan/ask/{licenseId}', name: 'app_license_create_payment_plan')]
    public function askingPaymentPlan(): Response {}

    // Find a licence
    private function findLicense(string $licenseId): License
    {
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

    // Find a licence with payments
    private function findLicenseWithPayments(string $licenseId): License
    {
        return $this->licenseRepository->findWithPayments($licenseId);
    }

    // Create Payment object
    private function createPayment(License $license, $paymentType, $paymentStatus): Payment
    {
        $payment = new Payment();
        $payment->setLicense($license);
        $payment->setPaymentType($paymentType);
        $payment->setStatus($paymentStatus);

        return $payment;
    }

    // Create PaymentOrder object
    private function createPaymentOrder(Payment $payment, License $license, DateTimeImmutable $dueDate = new \DateTimeImmutable(), DateTimeImmutable $valueDate = new \DateTimeImmutable()): PaymentOrder
    {
        $paymentOrder = new PaymentOrder();
        $paymentOrder->setPayment($payment);
        $paymentOrder->setAmount($license->getPrice());
        $paymentOrder->setDueDate($dueDate);
        $paymentOrder->setValueDate($valueDate);

        return $paymentOrder;
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
