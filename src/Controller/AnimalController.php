<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Form\AnimalType;
use App\Repository\AnimalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/animal')]
final class AnimalController extends AbstractController
{
    // Afficher tous les animaux
    #[Route(name: 'app_animal_index', methods: ['GET'])]
    public function index(AnimalRepository $animalRepository): Response
    {
        return $this->render('animal/index.html.twig', [
            'animals' => $animalRepository->findAll(),
        ]);
    }

    // Créer un nouvel animal
#[Route('/new', name: 'app_animal_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{ 
    $animal = new Animal();
    $form = $this->createForm(AnimalType::class, $animal);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Vérification et définition de valeurs par défaut
        if (empty($animal->getName())) {
            $animal->setName('Nom par défaut');  // Valeur par défaut pour name
        }
        
        if (empty($animal->getHealthStatus())) {
            $animal->setHealthStatus('Healthy');  // Valeur par défaut pour healthStatus
        }

        // Persist et flush l'entité
        $entityManager->persist($animal);
        $entityManager->flush();

        // Redirection après sauvegarde
        return $this->redirectToRoute('app_animal_index', [], Response::HTTP_SEE_OTHER);
    }

    // Rendu du formulaire
    return $this->render('animal/new.html.twig', [
        'animal' => $animal,
        'form' => $form,
    ]);
}

    // Afficher un animal spécifique
    #[Route('/{id}', name: 'app_animal_show', methods: ['GET'])]
    public function show(Animal $animal): Response
    {
        return $this->render('animal/show.html.twig', [
            'animal' => $animal,
        ]);
    }

    // Modifier un animal existant
    #[Route('/{id}/edit', name: 'app_animal_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Animal $animal, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification du champ name avant la mise à jour
            if (empty($animal->getName())) {
                // Assigner une valeur par défaut si le nom est vide
                $animal->setName('Nom par défaut');
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_animal_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('animal/edit.html.twig', [
            'animal' => $animal,
            'form' => $form,
        ]);
    }

    // Supprimer un animal
    #[Route('/{id}', name: 'app_animal_delete', methods: ['POST'])]
    public function delete(Request $request, Animal $animal, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$animal->getId(), $request->get('_token'))) {
            $entityManager->remove($animal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_animal_index', [], Response::HTTP_SEE_OTHER);
    }
}