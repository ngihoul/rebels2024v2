<?php

namespace App\Controller;

use App\Entity\Place;
use App\Form\PlaceType;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
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

        $placesCount = count($places);

        return $this->render('place/index.html.twig', [
            'places' => $places,
            'placesCount' => $placesCount,
        ]);
    }

    #[Route('/create', name: 'app_place_create')]
    public function create(Request $request): response
    {
        $action = 'create';

        try {
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
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_places');
        }
    }

    #[Route('/update/{placeId}', name: 'app_place_update')]
    public function update(Request $request, PlaceRepository $placeRepository, $placeId): response
    {
        $action = 'update';

        try {
            $place = $placeRepository->find($placeId);

            if (!$place) {
                throw new EntityNotFoundException('Le lieu n\'a pas été trouvé');
            }

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
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_places');
        }
    }

    // #[Route('/delete/{placeId}', name: 'app_places_delete')]
    // public function delete(): response
    // {
    //     return $this->render('place/index.html.twig', []);
    // }
}
