<?php

namespace App\Controller;

use App\Entity\License;
use App\Entity\Payment;
use App\Form\PaymentPlanType;
use App\Form\ValidateLicenseType;
use App\Repository\LicenseRepository;
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

    public function __construct(LicenseRepository $licenseRepository, EntityManagerInterface $entityManager, PaymentRepository $paymentRepository, TranslatorInterface $translator)
    {
        $this->licenseRepository = $licenseRepository;
        $this->entityManager = $entityManager;
        $this->paymentRepository = $paymentRepository;
        $this->translator = $translator;
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
    public function validateLicense(Request $request, EmailManager $emailManager, TranslatorInterface $translator): Response
    {
        try {
            $licenseId = $request->get('licenseId');
            $license = $this->licenseRepository->find($licenseId);

            if (!$license) {
                throw new EntityNotFoundException($translator->trans('error.license.not_found'));
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
                    $emailManager->sendEmail($license->getUser()->getEmail(), $translator->trans('license.approved.subject', [], 'emails'), 'license_approved');
                } elseif ($form->get('refusal')->isClicked()) {
                    // Change status to ON_DEMAND
                    $license->setStatus(License::ON_DEMAND);

                    // Add comment to License
                    $license->setComment($form->get('comment')->getData());

                    // TODO : Fetch locale from $license->getUser()

                    // Send a mail to player informing him his license has been refused
                    $emailManager->sendEmail($license->getUser()->getEmail(), $translator->trans('license.refused.subject', [], 'emails'), 'license_refused', ['reason' => $license->getComment()]);
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
        $paymentPlanRequests = $this->paymentRepository->findPaymentPlanRequestToValidate(10);

        // $paymentOrdersToValidate = $this->paymentRepository->findPaymentOrderToValidate(10);

        return $this->render('admin/payment/index.html.twig', [
            'paymentPlanRequests' => $paymentPlanRequests,
            // 'paymentOrdersToValidate' => $paymentOrdersToValidate
        ]);
    }

    // Display a payment plan request
    #[Route('/licenses/payment_plan/{planId}', name: 'admin_payment_plan_request')]
    public function paymentPlanRequest(Request $request): Response
    {
        $paymentPlan = $this->findPayment($request);

        return $this->render('admin/payment/detail.html.twig', [
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
    public function paymentPlanRefuse(Request $request): Response {}

    // Find a payment
    private function findPayment($request): Payment
    {
        $planId = $request->get('planId');
        $payment = $this->paymentRepository->find($planId);

        return $payment;
    }
}
