<?php

namespace App\Controller;

use App\Entity\SoinDesAnimaux;
use App\Form\SoinDesAnimauxType;
use App\Repository\SoinDesAnimauxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/soin/des/animaux')]
final class SoinDesAnimauxController extends AbstractController
{
    #[Route(name: 'app_soin_des_animaux_index', methods: ['GET'])]
    public function index(SoinDesAnimauxRepository $soinDesAnimauxRepository): Response
    {
        return $this->render('soin_des_animaux/index.html.twig', [
            'soin_des_animauxes' => $soinDesAnimauxRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_soin_des_animaux_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $soinDesAnimaux = new SoinDesAnimaux();
        $form = $this->createForm(SoinDesAnimauxType::class, $soinDesAnimaux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($soinDesAnimaux);
            $entityManager->flush();

            return $this->redirectToRoute('app_soin_des_animaux_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('soin_des_animaux/new.html.twig', [
            'soin_des_animaux' => $soinDesAnimaux,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_soin_des_animaux_show', methods: ['GET'])]
    public function show(SoinDesAnimaux $soinDesAnimaux): Response
    {
        return $this->render('soin_des_animaux/show.html.twig', [
            'soin_des_animaux' => $soinDesAnimaux,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_soin_des_animaux_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SoinDesAnimaux $soinDesAnimaux, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SoinDesAnimauxType::class, $soinDesAnimaux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_soin_des_animaux_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('soin_des_animaux/edit.html.twig', [
            'soin_des_animaux' => $soinDesAnimaux,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_soin_des_animaux_delete', methods: ['POST'])]
    public function delete(Request $request, SoinDesAnimaux $soinDesAnimaux, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$soinDesAnimaux->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($soinDesAnimaux);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_soin_des_animaux_index', [], Response::HTTP_SEE_OTHER);
    }
}
