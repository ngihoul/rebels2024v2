<?php

namespace App\Controller;

use App\Entity\License;
use App\Entity\Payment;
use App\Entity\PaymentOrder;
use App\Form\PaymentPlanRequestType;
use App\Repository\LicenseRepository;
use App\Repository\PaymentOrderRepository;
use App\Repository\PaymentRepository;
use App\Service\EmailManager;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LicenseRepository $licenseRepository;
    private PaymentOrderRepository $paymentOrderRepository;
    private PaymentRepository $paymentRepository;
    private TranslatorInterface $translator;
    private EmailManager $emailManager;

    public function __construct(EntityManagerInterface $entityManager, LicenseRepository $licenseRepository, PaymentOrderRepository $paymentOrderRepository, PaymentRepository $paymentRepository, TranslatorInterface $translator, EmailManager $emailManager)
    {
        $this->entityManager = $entityManager;
        $this->licenseRepository = $licenseRepository;
        $this->paymentOrderRepository = $paymentOrderRepository;
        $this->paymentRepository = $paymentRepository;
        $this->translator = $translator;
        $this->emailManager = $emailManager;
    }

    // Route to pay the licence via Stripe
    #[Route('/license/checkout/{licenseId}', name: 'app_license_checkout')]
    #[IsGranted('ROLE_USER')]
    public function licenseCheckout(Request $request)
    {
        try {
            $license = $this->findLicense($request);

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
                'success_url' => $this->generateUrl('app_license_success_payment', ['licenseId' => $license->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('app_cancel_payment', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'client_reference_id' => $license->getId(),
                'metadata' => [
                    'payment_type' => 'license'
                ],
            ]);

            return $this->redirect($checkout_session->url, 303);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_license');
        }
    }

    // Route to pay a payment order via STripe
    #[Route('/payment_order/checkout/{orderId}', name: 'app_payment_order_checkout')]
    #[IsGranted('ROLE_USER')]
    public function paymentOrderCheckout(Request $request)
    {
        try {
            $order = $this->findPaymentOrder($request);

            $this->restrictAccessIfUserIsNotOwnerOf($order->getPayment()->getLicense());

            Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

            $checkout_session = Session::create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Ordre de paiement',
                        ],
                        'unit_amount' => $order->getAmount() * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('app_order_success_payment', ['orderId' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('app_cancel_payment', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'client_reference_id' => $order->getId(),
                'metadata' => [
                    'payment_type' => 'order'
                ],
            ]);

            return $this->redirect($checkout_session->url, 303);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_license');
        }
    }

    #[Route('/webhook/stripe', name: 'app_webhook_stripe')]
    public function webhook(Request $request): Response
    {
        $endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'];

        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (Exception $e) {
            return new Response('Invalid payload or signature', Response::HTTP_BAD_REQUEST);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $paymentType = $session->metadata->payment_type ?? null;
            // LicenseId or OrderId
            $objectId = $session->client_reference_id;

            if ($paymentType === 'license') {
                $license = $this->licenseRepository->find($objectId);

                // Create Payment Object
                $payment = $this->createPayment($license, Payment::BY_STRIPE, Payment::STATUS_COMPLETED);
                $this->entityManager->persist($payment);

                // Create PaymentOrder
                $paymentOrder = $this->createPaymentOrder($payment, $license);
                $this->entityManager->persist($paymentOrder);

                // Update License status
                $license->setStatus(License::IN_ORDER);
                $this->entityManager->persist($license);

                // Save objects in DB
                $this->entityManager->flush();
            } elseif ($paymentType === 'order') {
                $order = $this->paymentOrderRepository->find($objectId);
                $license = $order->getPayment()->getLicense();

                // Update PaymentOrder
                $this->validateOrder($order);

                if ($this->isFullyPaid($license)) {
                    $this->setLicenseInOrder($license);
                }
                $this->entityManager->persist($order);

                // Save objects in DB
                $this->entityManager->flush();
            }
        }

        return new Response('Success', Response::HTTP_OK);
    }

    // Stripe payment success for license payment
    #[Route('/license/success-url/{licenseId}', name: 'app_license_success_payment')]
    #[IsGranted('ROLE_USER')]
    public function licenseSuccessUrl(Request $request): Response
    {
        return $this->render('payment/success_license.html.twig', []);
    }

    // Stripe payment success
    #[Route('/payment_order/success-url/{orderId}', name: 'app_order_success_payment')]
    #[IsGranted('ROLE_USER')]
    public function paymentOrderuccessUrl(Request $request): Response
    {
        return $this->render('payment/success_order.html.twig', []);
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
        $license = $this->findLicense($request);

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
        $license = $this->findLicenseWithPayments($request);

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
    #[Route('/payment_plan/request/{licenseId}', name: 'app_license_request_payment_plan')]
    public function paymentPlanRequest(Request $request): Response
    {
        try {
            $license = $this->findLicense($request);
            $form = $this->createForm(PaymentPlanRequestType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $payment = $this->createPayment($license, Payment::BY_PAYMENT_PLAN, Payment::STATUS_PENDING);
                $payment->setUserComment($form->get('user_comment')->getData());
                $this->entityManager->persist($payment);

                $this->entityManager->flush();

                // Always sent in French -- No need to translate
                $this->emailManager->sendEmail(EmailManager::ADMIN_MAIL, "Nouvelle demande de plan de paiement", 'payment_plan_request', [
                    'payment' => $payment
                ]);

                $this->addFlash('success', $this->translator->trans('success.payment_plan_request'));

                return $this->redirectToRoute('app_license');
            }

            return $this->render('license/form_request_payment_plan.html.twig', [
                'form' => $form->createView(),
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_license');
        }
    }

    // TODO : Create service (LicenseService & OrderService ?) with these private functions

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

    private function findPaymentOrder(Request $request): PaymentOrder
    {
        $paymentOrderId = $request->get('orderId');
        $paymentOrder = $this->paymentOrderRepository->find($paymentOrderId);

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
    // Find a licence with payments
    private function findLicenseWithPayments(Request $request): License
    {
        $licenseId = $request->get('licenseId');
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

    private function validateOrder(PaymentOrder $order): void
    {
        $order->setValueDate(new \DateTimeImmutable());
        $order->setValidatedBy($this->getUser());
    }

    private function isFullyPaid(License $license): bool
    {
        $payment = $this->paymentRepository->findBy(['license' => $license, 'status' => Payment::STATUS_ACCEPTED]);
        $payment = $payment[0] ? $payment[0] : null;

        $actualyPaid = 0;

        if ($payment) {

            foreach ($payment->getPaymentOrders() as $order) {
                if ($order->getValueDate()) {
                    $actualyPaid += $order->getAmount();
                }
            }
        }

        return $actualyPaid >= $license->getPrice();
    }

    private function setLicenseInOrder(License $license): void
    {
        $license->setStatus(License::IN_ORDER);

        // Delete remaining orders // Shouldn't be called !
        $this->cleanRemainingOrders($license);

        // Sent a mail to user
        $this->emailManager->sendEmail($license->getUser()->getEmail(), $this->translator->trans('license.in_order.subject', [], 'emails'), 'license_in_order');
    }

    private function cleanRemainingOrders(License $license): void
    {
        foreach ($license->getPayments() as $payment) {
            foreach ($payment->getPaymentOrders() as $order) {
                if ($order->getValueDate() === null) {
                    $this->entityManager->remove($order);
                }
            }
        }

        $this->entityManager->flush();
    }
}
