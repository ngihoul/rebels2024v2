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
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/places')]
#[IsGranted('ROLE_COACH')]
class PlaceController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private PlaceRepository $placeRepository;
    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, PlaceRepository $placeRepository, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->placeRepository = $placeRepository;
        $this->translator = $translator;
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
                    $this->addFlash('error', $this->translator->trans('error.place.already_exist'));
                    // TODO : Redirect to place profile of existingPlace
                    return $this->redirectToRoute('app_place_detail', ['placeId' => $existingPlace->getId()]);
                }

                $this->entityManager->persist($place);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('success.place.created'));
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
    public function update(Request $request, $placeId): response
    {
        $action = 'update';

        try {
            $place = $this->findPlace($placeId);

            $form = $this->createForm(PlaceType::class, $place);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $place = $form->getData();

                $this->entityManager->persist($place);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('success.place.updated'));
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

    #[Route('/{placeId}', name: 'app_place_detail')]
    public function detail($placeId): Response
    {
        try {
            $place = $this->findPlace($placeId);

            return $this->render('place/detail.html.twig', [
                'place' => $place
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

    private function findPlace($placeId)
    {
        $place = $this->placeRepository->find($placeId);

        if (!$place) {
            throw new EntityNotFoundException($this->translator->trans('error.place.not_found'));
        }

        return $place;
    }
}
