<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Commentaire;
use App\Entity\User;

use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/produit')]
final class ProduitController extends AbstractController
{
    #[Route(name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Get the currently logged-in user
            /* $user = $security->getUser();
            if (!$user) {
                throw new \Exception("You must be logged in to add a product.");
            } */
            // Associate the product with the logged-in user
            $user = $entityManager->getRepository(User::class)->find(3);
            $produit->setVendeur($user);
            // Handle file upload
            /** @var UploadedFile $pictureFile */
            $pictureFile = $form->get('picture')->getData();

            if ($pictureFile) {
                // Generate a unique filename
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();

                // Move the file to the uploads directory
                $pictureFile->move(
                    $this->getParameter('uploads_directory'), // Defined in services.yaml
                    $newFilename
                );

                // Save the file path in the database
                $produit->setPicture('uploads/'.$newFilename);
            }

            // Save the product
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('app_marketplace', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/produit/{id}/commentaires/add", name="produit_add_commentaire", methods={"POST"})
     */
    #[Route('/{id}/commentaires/add', name: 'app_add_produit_commentaire', methods: ['POST'])]
    public function addComment($id, Request $request, EntityManagerInterface $entityManager)
    {
        $produit = $entityManager->getRepository(Produit::class)->find($id);

        if (!$produit) {
            return new JsonResponse(["success" => false, "error" => "Produit not found"], 404);
        }

        $user = $entityManager->getRepository(User::class)->find(3); // get the testing user

        // Decode the JSON request body
        $data = json_decode($request->getContent(), true);

        if (!isset($data['contenu']) || empty($data['contenu'])) {
            return new JsonResponse(["success" => false, "error" => "Comment content is required"], 400);
        }

        // Create new Commentaire instance
        $commentaire = new Commentaire();
        $commentaire->setContenu($data['contenu']);
        $commentaire->setProduit($produit);
        $commentaire->setCreatedAt(new \DateTime());
        $commentaire->setAuteur($user);


        $entityManager->persist($commentaire);
        $entityManager->flush();

        // Return JSON response
        return new JsonResponse(["success" => true, "message" => "Comment added successfully"]);
    }

    #[Route('/{id}/comments', name: 'app_produit_show_comments', methods: ['GET'])]
    public function showreplies(Produit $produit, CommentaireRepository $commentaireRepository): JsonResponse
    {
        // Fetch top-level comments (comments with no parent)
        $topLevelComments = $commentaireRepository->findBy(['produit' => $produit, 'parent' => null]);

        // Serialize comments and their replies
        $comments = [];
        foreach ($topLevelComments as $comment) {
            $comments[] = [
                'id' => $comment->getId(),
                'contenu' => $comment->getContenu(),
                'auteur' => [
                    'username' => $comment->getAuteur()->getUsername(),
                    'image' => $comment->getAuteur()->getImage(),
                ],
                'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i'),
                'replies' => $this->serializeReplies($comment->getReplies()),
            ];
        }

        return new JsonResponse(['comments' => $comments]);
    }

    // Helper method to serialize replies recursively
    private function serializeReplies(iterable $replies): array
    {
        $serializedReplies = [];

        foreach ($replies as $reply) {
            $serializedReplies[] = [
                'id' => $reply->getId(),
                'contenu' => $reply->getContenu(),
                'auteur' => [
                    'username' => $reply->getAuteur()->getUsername(),
                    'image' => $reply->getAuteur()->getImage(),
                ],
                'createdAt' => $reply->getCreatedAt()->format('Y-m-d H:i'),
                'replies' => $this->serializeReplies($reply->getReplies()), // Recursively serialize nested replies
            ];
        }

        return $serializedReplies;
    }



    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            /** @var UploadedFile $pictureFile */
            $pictureFile = $form->get('picture')->getData();

            if ($pictureFile) {
                // Generate a unique filename
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();

                // Move the file to the uploads directory
                $pictureFile->move(
                    $this->getParameter('uploads_directory'), // Defined in services.yaml
                    $newFilename
                );

                // Delete the old picture if it exists
                if ($produit->getPicture()) {
                    $oldPicturePath = $this->getParameter('uploads_directory').'/'.basename($produit->getPicture());
                    if (file_exists($oldPicturePath)) {
                        unlink($oldPicturePath);
                    }
                }

                // Save the new file path in the database
                $produit->setPicture('uploads/'.$newFilename);
            }

            // Save the product
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->getPayload()->getString('_token'))) {
            // Delete the picture file if it exists
            if ($produit->getPicture()) {
                $picturePath = $this->getParameter('uploads_directory').'/'.basename($produit->getPicture());
                if (file_exists($picturePath)) {
                    unlink($picturePath);
                }
            }

            // Remove the product
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}