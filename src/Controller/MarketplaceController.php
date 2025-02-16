<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MarketplaceController extends AbstractController
{
    // API Endpoint: Fetch products based on category
    #[Route('/marketplace/fetch-products', name: 'fetch_products', methods: ['GET'])]
    public function fetchProducts(Request $request, ProduitRepository $produitRepository): JsonResponse
    {
        $categoryId = $request->query->get('category');
        $searchQuery = $request->query->get('search');
        $sortBy = $request->query->get('sortBy'); // New: Get the sorting parameter

        // Fetch products based on category, search query, and sorting
        if ($categoryId && $searchQuery) {
            // Fetch products by category and search query, with sorting
            $products = $produitRepository->findByCategoryAndSearchQuery($categoryId, $searchQuery, $sortBy);
        } elseif ($categoryId) {
            // Fetch products by category only, with sorting
            $products = $produitRepository->findByCategoryAndSort($categoryId, $sortBy);
        } elseif ($searchQuery) {
            // Fetch products by search query only, with sorting
            $products = $produitRepository->findBySearchQuery($searchQuery, $sortBy);
        } else {
            // No category or search query provided
            $products = [];
        }

        // Prepare the data for JSON response
        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'nom' => $product->getNom(),
                'description' => $product->getDescription(),
                'prix' => $product->getPrix(),
                'quantite' => $product->getQuantite(),
                'picture' => $product->getPicture(),
            ];
        }

        return new JsonResponse($data);
    }

    // Rendered Page: Main marketplace page
    #[Route('/marketplace', name: 'app_marketplace', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository)
    {
        $categories = $categorieRepository->findAll();

        return $this->render('marketplace/index.html.twig', [
            'categories' => $categories,
        ]);
    }
}
