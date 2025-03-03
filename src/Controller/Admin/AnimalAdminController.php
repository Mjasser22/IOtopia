<?php

namespace App\Controller\Admin;

use App\Entity\Animal;
use App\Repository\AnimalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/animaux')]
class AnimalAdminController extends AbstractController
{
    #[Route('/', name: 'admin_animal_index')]
    public function index(AnimalRepository $repository)
    {
        return $this->render('admin/animals/index.html.twig', [
            'animals' => $repository->findAllWithDetails(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_animal_edit')]
    public function edit(Animal $animal)
    {
        // Logique d'édition
    }

    #[Route('/{id}/delete', name: 'admin_animal_delete')]
    public function delete(Animal $animal)
    {
        // Logique de suppression
    }
}