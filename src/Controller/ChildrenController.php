<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChildType;
use App\Repository\RelationTypeRepository;
use App\Service\ProfilePictureManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/children')]
class ChildrenController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private RelationTypeRepository $relationTypeRepository;
    private TranslatorInterface $translator;
    private ProfilePictureManager $profilePictureManager;

    public function __construct(EntityManagerInterface $entityManager, RelationTypeRepository $relationTypeRepository, TranslatorInterface $translator, ProfilePictureManager $profilePictureManager)
    {
        $this->entityManager = $entityManager;
        $this->relationTypeRepository = $relationTypeRepository;
        $this->translator = $translator;
        $this->profilePictureManager = $profilePictureManager;
    }

    #[Route('/create', name: 'app_children_create')]
    public function create(Request $request): Response
    {
        $action = 'create';

        $child = new User();
        $form = $this->createForm(ChildType::class, $child);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $relationTypeId = $form->get('relation_type')->getData();
            $relationType = $this->relationTypeRepository->find($relationTypeId);

            if (!$relationType) {
                $this->addFlash('error', $this->translator->trans('error.relation_type_not_found'));
                return $this->redirectToRoute('app_register');
            }

            /** @var User $user */
            $user = $this->getUser();

            $sameAdressAsParent = $form->get('same_address_as_parent')->getData();
            if ($sameAdressAsParent) {
                $child->setAddressStreet($user->getAddressStreet());
                $child->setAddressNumber($user->getAddressNumber());
                $child->setZipCode($user->getZipCode());
                $child->setLocality($user->getLocality());
                $child->setCountry($user->getCountry());
            }

            $child->setParent($user, $relationType);

            // If the user is not yet a parent, add the role
            $userRoles = $user->getRoles();
            if (!in_array('ROLE_PARENT', $userRoles)) {
                $userRoles[] = 'ROLE_PARENT';
                $user->setRoles($userRoles);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                // To keep the user connected > update the security token
                $token = new UsernamePasswordToken($user, 'main', $userRoles);
                $this->container->get('security.token_storage')->setToken($token);

                $this->container->get('session')->set('_security_main', serialize($token));
            }

            // Not used for now
            $child->setRoles(['ROLE_CHILD']);

            try {
                $this->profilePictureManager->handleProfilePicture($form, $child);
            } catch (\Exception $e) {
                $this->addFlash('error', $this->translator->trans('error.profile_picture'));
                return $this->redirectToRoute('app_children_create');
            }

            $this->entityManager->persist($child);

            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('success.children_created'));
            $this->entityManager->persist($child);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('children/form.html.twig', [
            'form' => $form->createView(),
            'action' => $action
        ]);
    }
}
