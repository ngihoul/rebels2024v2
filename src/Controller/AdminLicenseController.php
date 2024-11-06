<?php

namespace App\Controller;

use App\Entity\License;
use App\Entity\Payment;
use App\Entity\PaymentOrder;
use App\Form\PaymentOrderValidationType;
use App\Form\PaymentPlanRefuseType;
use App\Form\PaymentPlanType;
use App\Form\ValidateLicenseType;
use App\Repository\LicenseRepository;
use App\Repository\PaymentOrderRepository;
use App\Repository\PaymentRepository;
use App\Service\EmailManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminLicenseController extends AbstractController
{
    private LicenseRepository $licenseRepository;
    private EntityManagerInterface $entityManager;
    private PaymentRepository $paymentRepository;
    private TranslatorInterface $translator;
    private PaymentOrderRepository $paymentOrderRepository;

    public function __construct(LicenseRepository $licenseRepository, EntityManagerInterface $entityManager, PaymentRepository $paymentRepository, TranslatorInterface $translator, PaymentOrderRepository $paymentOrderRepository)
    {
        $this->licenseRepository = $licenseRepository;
        $this->entityManager = $entityManager;
        $this->paymentRepository = $paymentRepository;
        $this->translator = $translator;
        $this->paymentOrderRepository = $paymentOrderRepository;
    }

    // Display all licences to validate
    #[Route('/licenses', name: 'admin_license_to_validate')]
    public function licences(): Response
    {
        $licenses = $this->licenseRepository->findAllLicensesToValidate();

        return $this->render('admin/license/list.html.twig', [
            'licenses' => $licenses,
        ]);
    }

    // Validate or refuse a licence request
    #[Route('/validate-license/{licenseId}', name: 'admin_validate_license')]
    public function validateLicense(Request $request, EmailManager $emailManager): Response
    {
        try {
            $licenseId = $request->get('licenseId');
            $license = $this->licenseRepository->find($licenseId);

            if (!$license) {
                throw new EntityNotFoundException($this->translator->trans('error.license.not_found'));
            }

            $form = $this->createForm(ValidateLicenseType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Check which button was clicked 
                if ($form->get('approval')->isClicked()) {
                    // Change status to DOC_VALIDATED
                    $license->setStatus(License::DOC_VALIDATED);

                    // Add price to License
                    $license->setPrice($form->get('price')->getData());

                    // TODO : Fetch locale from $license->getUser()

                    // Send a mail to player informing him his license has been validated
                    $emailManager->sendEmail($license->getUser()->getEmail(), $this->translator->trans('license.approved.subject', [], 'emails'), 'license_approved');
                } elseif ($form->get('refusal')->isClicked()) {
                    // Change status to ON_DEMAND
                    $license->setStatus(License::ON_DEMAND);

                    // Add comment to License
                    $license->setComment($form->get('comment')->getData());

                    // TODO : Fetch locale from $license->getUser()

                    // Send a mail to player informing him his license has been refused
                    $emailManager->sendEmail($license->getUser()->getEmail(), $this->translator->trans('license.refused.subject', [], 'emails'), 'license_refused', ['reason' => $license->getComment()]);
                }

                $this->entityManager->persist($license);
                $this->entityManager->flush();

                // Redirect to list of license to validate
                return $this->redirectToRoute('admin_license_to_validate');
            }

            return $this->render('admin/license/validate.html.twig', [
                'form' => $form->createView(),
                'license' => $license
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_license_to_validate');
        }
    }

    // Display payment dashboard
    #[Route('/payments', name: 'admin_payments')]
    public function payments(): Response
    {
        $paymentPlanRequests = $this->paymentRepository->findPaymentPlanRequestsToValidate(10);

        $paymentOrdersToValidate = $this->paymentOrderRepository->findPaymentOrdersToValidate(10);

        return $this->render('admin/payment/index.html.twig', [
            'paymentPlans' => $paymentPlanRequests,
            'paymentOrders' => $paymentOrdersToValidate
        ]);
    }

    // Display a payment plan request
    #[Route('/licenses/payment_plan/{planId}', name: 'admin_payment_plan_request')]
    public function paymentPlanRequest(Request $request): Response
    {
        $paymentPlan = $this->findPayment($request);

        return $this->render('admin/payment/plan_detail.html.twig', [
            'paymentPlan' => $paymentPlan
        ]);
    }

    // Accept a payment plan
    #[Route('/licenses/payment_plan/{planId}/validate', name: 'admin_payment_plan_validate')]
    public function paymentPlanValidate(Request $request): Response
    {
        // TODO : try .. catch ...
        $paymentPlan = $this->findPayment($request);

        $form = $this->createForm(PaymentPlanType::class, $paymentPlan);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update status payment
            $paymentPlan->setStatus(Payment::STATUS_ACCEPTED);

            // Save payment
            $this->entityManager->persist($paymentPlan);
            $this->entityManager->flush();

            // TODO : Send mail to user to confirm

            $this->addFlash('success', $this->translator->trans('success.payment_plan.validated'));

            return $this->redirectToRoute('admin_payments');
        }

        return $this->render('admin/payment/accept.html.twig', [
            'paymentPlan' => $paymentPlan,
            'form' => $form->createView()
        ]);
    }

    // Refuse a payment plan
    #[Route('/licenses/payment_plan/{planId}/refuse', name: 'admin_payment_plan_refuse')]
    public function paymentPlanRefuse(Request $request): Response
    {
        $payment = $this->findPayment($request);

        $form = $this->createForm(PaymentPlanRefuseType::class, $payment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $payment->setStatus(Payment::STATUS_REFUSED);

            $this->entityManager->persist($payment);
            $this->entityManager->flush();

            // TODO : Send mail to user


            $this->addFlash('success', $this->translator->trans('success.payment_plan.refused'));

            return $this->redirectToRoute('admin_payments');
        }

        return $this->render('admin/payment/refuse.html.twig', [
            'paymentPlan' => $payment,
            'form' => $form->createView()
        ]);
    }

    #[Route('/licenses/payment_order/{orderId}', name: 'admin_payment_order_detail')]
    public function paymentOrderDetail(Request $request): Response
    {
        $order = $this->findPaymentOrder($request);

        $form = $this->createForm(PaymentOrderValidationType::class, $order, ['paymentOrder' => $order]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->validatePaymentOrder($request, $order);
        }

        return $this->render('admin/payment/order_detail.html.twig', [
            'paymentPlan' => $order->getPayment(),
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    // Quick payment order validation
    #[Route('/licenses/payment_order/{orderId}/quick_validate', name: 'admin_payment_order_quick_validate')]
    public function paymentOrderQuickValidate(Request $request): Response
    {
        $order = $this->findPaymentOrder($request);

        return $this->validatePaymentOrder($request, $order);
    }

    // Find a payment
    private function findPayment($request): Payment
    {
        $planId = $request->get('planId');
        $payment = $this->paymentRepository->find($planId);

        return $payment;
    }

    // Find a payment order
    private function findPaymentOrder($request): PaymentOrder
    {
        $orderId = $request->get('orderId');
        $order = $this->paymentOrderRepository->find($orderId);

        return $order;
    }

    // Check if a license is fully paid
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

    private function validateOrder(PaymentOrder $order): void
    {
        $order->setValueDate(new \DateTimeImmutable());
        $order->setValidatedBy($this->getUser());
    }

    private function setLicenseInOrder(License $license): void
    {
        $license->setStatus(License::IN_ORDER);

        // Delete remaining orders // Shouldn't be called !
        $this->cleanRemainingOrders($license);

        // TODO : Sent a mail to user
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

    private function validatePaymentOrder(Request $request, PaymentOrder $order): Response
    {
        $this->validateOrder($order);

        $license = $order->getPayment()->getLicense();

        if ($this->isFullyPaid($license)) {
            $this->setLicenseInOrder($license);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('success.payment_order.validated'));

        return $this->redirectToRoute('admin_payments');
    }
}
