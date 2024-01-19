<?php

namespace App\Controller;

use App\Entity\License;
use App\Form\ValidateLicenseType;
use App\Repository\LicenseRepository;
use App\Service\EmailManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminLicenseController extends AbstractController
{
    private LicenseRepository $licenseRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(LicenseRepository $licenseRepository, EntityManagerInterface $entityManager)
    {
        $this->licenseRepository = $licenseRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/admin/licenses', name: 'admin_license_to_validate')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $licenses = $this->licenseRepository->findAllLicensesToValidate();

        return $this->render('admin/license/list.html.twig', [
            'licenses' => $licenses,
        ]);
    }

    #[Route('/admin/validate-license/{licenseId}', name: 'admin_validate_license')]
    #[IsGranted('ROLE_ADMIN')]
    public function validate(Request $request, EmailManager $emailManager): Response
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
            return $this->redirectToRoute('app_license');
        }

        $form = $this->createForm(ValidateLicenseType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('approval')->isClicked()) {
                // Change status to DOC_VALIDATED
                $license->setStatus(License::DOC_VALIDATED);

                // Add price to License
                $license->setPrice($form->get('price')->getData());

                // Send a mail to player informing him his license has been validated
                $emailManager->sendEmail($license->getUser()->getEmail(), 'Licence validée', 'license_approved');
            } elseif ($form->get('refusal')->isClicked()) {
                // Change status to ON_DEMAND
                $license->setStatus(License::ON_DEMAND);

                // Add comment to License
                $license->setComment($form->get('comment')->getData());

                // Send a mail to player informing him his license has been refused
                $emailManager->sendEmail($license->getUser()->getEmail(), 'Licence refusée', 'license_refused', ['reason' => $license->getComment()]);
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
    }
}
