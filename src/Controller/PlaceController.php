<?php

namespace App\Controller;

use App\Entity\Place;
use App\Form\PlaceType;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/places')]
#[IsGranted('ROLE_COACH')]
class PlaceController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private PlaceRepository $placeRepository;

    public function __construct(EntityManagerInterface $entityManager, PlaceRepository $placeRepository)
    {
        $this->entityManager = $entityManager;
        $this->placeRepository = $placeRepository;
    }

    #[Route('/', name: 'app_places')]
    public function index(): Response
    {
        $places = $this->placeRepository->findBy([], ['name' => "ASC"]);

        return $this->render('place/index.html.twig', [
            'places' => $places,
        ]);
    }

    #[Route('/create', name: 'app_places_create')]
    public function create(Request $request): response
    {
        $action = 'create';

        $place = new Place();
        $form = $this->createForm(PlaceType::class, $place);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $place = $form->getData();

            // Check if new place is not already in DB based on street & zipcode
            $existingPlace = $this->placeRepository->findOneBy([
                'address_street' => $place->getAddressStreet(),
                'address_zipcode' => $place->getAddressZipcode(),
            ]);

            if ($existingPlace) {
                $this->addFlash('error', 'Un lieu avec le même nom de rue et le même code postal existe déjà.');
                // TODO : Redirect to place profile of existingPlace
                return $this->redirectToRoute('app_places_create');
            }

            $this->entityManager->persist($place);
            $this->entityManager->flush();

            $this->addFlash('success', 'Lieu créé');
            return $this->redirectToRoute('app_places');
        }

        return $this->render('place/form.html.twig', [
            'action' => $action,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/update/{placeId}', name: 'app_places_update')]
    public function update(Request $request, PlaceRepository $placeRepository, $placeId): response
    {
        $action = 'update';

        $place = $placeRepository->find($placeId);
        $form = $this->createForm(PlaceType::class, $place);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $place = $form->getData();

            $this->entityManager->persist($place);
            $this->entityManager->flush();

            $this->addFlash('success', 'Lieu modifié');
            return $this->redirectToRoute('app_places');
        }

        return $this->render('place/form.html.twig', [
            'action' => $action,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{placeId}', name: 'app_places_delete')]
    public function delete(): response
    {
        return $this->render('place/index.html.twig', []);
    }
}
