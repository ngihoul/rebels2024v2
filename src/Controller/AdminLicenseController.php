<?php

namespace App\Controller;

use App\Entity\License;
use App\Form\ValidateLicenseType;
use App\Repository\LicenseRepository;
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

    public function __construct(LicenseRepository $licenseRepository, EntityManagerInterface $entityManager)
    {
        $this->licenseRepository = $licenseRepository;
        $this->entityManager = $entityManager;
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

    #[Route('/licenses/payments', name: 'admin_license_payments')]
    public function payments(): Response {}
}
