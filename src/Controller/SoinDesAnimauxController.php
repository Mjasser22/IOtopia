<?php 
namespace App\Controller;

use App\Entity\SoinDesAnimaux;
use App\Form\SoinDesAnimauxType;
use App\Repository\SoinDesAnimauxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Users\HP\Desktop\Sabeel\app ;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/soin/des/animaux')]
final class SoinDesAnimauxController extends AbstractController
{
    #[Route('/soin/animaux', name: 'app_soin_des_animaux_index')]
public function index(SoinDesAnimauxRepository $repository): Response
{
    return $this->render('soin_des_animaux/index.html.twig', [
        'soins_des_animaux' => $repository->findAll(), // Nom de variable corrigé
    ]);
}

    // Show a single SoinDesAnimaux
    

    // Create new SoinDesAnimaux
    #[Route('/new', name: 'app_soin_des_animaux_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $soinDesAnimaux = new SoinDesAnimaux();
        $form = $this->createForm(SoinDesAnimauxType::class, $soinDesAnimaux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($soinDesAnimaux);
            $entityManager->flush();

            return $this->redirectToRoute('app_soin_des_animaux_index');
        }

        return $this->render('soin_des_animaux/new.html.twig', [
            'soin_des_animaux' => $soinDesAnimaux,
            'form' => $form->createView(),
        ]);
    }

    // Edit existing SoinDesAnimaux
    #[Route('/{id}/edit', name: 'app_soin_des_animaux_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SoinDesAnimaux $soinDesAnimaux, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SoinDesAnimauxType::class, $soinDesAnimaux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_soin_des_animaux_index');
        }

        return $this->render('soin_des_animaux/edit.html.twig', [
            'soin_des_animaux' => $soinDesAnimaux,
            'form' => $form->createView(),
        ]);
    }


    // Delete a SoinDesAnimaux
    #[Route('/{id}/delete', name: 'app_soin_des_animaux_delete', methods: ['POST'])]
    public function delete(Request $request, SoinDesAnimaux $soinDesAnimaux, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $soinDesAnimaux->getId(), $request->request->get('_token'))) {
            $entityManager->remove($soinDesAnimaux);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_soin_des_animaux_index');
    }

    #[Route('/{id}', name: 'app_soin_des_animaux_show', methods: ['GET'])] // Removed redundant 'soin_des_animaux' in URL path
    public function show(SoinDesAnimaux $soinDesAnimaux): Response
    {
        return $this->render('soin_des_animaux/show.html.twig', [
            'soinDesAnimaux' => $soinDesAnimaux, // Changed variable name to match the entity's name
        ]);
    }
    #[Route('/events', name: 'app_soin_des_animaux_events', methods: ['GET'])]
    public function events(SoinDesAnimauxRepository $soinDesAnimauxRepository): JsonResponse
    {
        // Récupérer tous les soins
        $soins = $soinDesAnimauxRepository->findAll();

        // Formater les données pour FullCalendar
        $events = [];
        foreach ($soins as $soin) {
            $events[] = [
                'title' => $soin->getAnimal()->getName() . ' - ' . $soin->getDescription(),
                'start' => $soin->getDate()->format('Y-m-d\TH:i:s'), // Format ISO 8601
                'url' => $this->generateUrl('app_soin_des_animaux_show', ['id' => $soin->getId()]),
            ];
        }

        return new JsonResponse($events);
    }
}
