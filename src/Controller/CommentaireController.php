<?php

namespace App\Controller;

use App\Entity\User;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/commentaire')]
final class CommentaireController extends AbstractController
{
    #[Route(name: 'app_commentaire_index', methods: ['GET'])]
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $commentaireRepository->findAll(),
        ]);
    }

    // src/Controller/CommentaireController.php

    #[Route('/{id}/reply', name: 'app_commentaire_reply', methods: ['POST'])]
    public function reply(Request $request, Commentaire $parentComment, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate the reply content
        if (!isset($data['contenu']) || empty($data['contenu'])) {
            return new JsonResponse(["success" => false, "error" => "Reply content is required"], 400);
        }

        $user = $entityManager->getRepository(User::class)->find(3);

        // Create the reply
        $reply = new Commentaire();
        $reply->setContenu($data['contenu']);
        $reply->setParent($parentComment);
        $reply->setAuteur($user);
        $reply->setProduit($parentComment->getProduit());

        // Save the reply
        $entityManager->persist($reply);
        $entityManager->flush();

        return new JsonResponse(["success" => true, "message" => "Reply added successfully"]);
    }

    #[Route('/new', name: 'app_commentaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commentaire/new.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_show', methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commentaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($commentaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/delete', name: 'app_delete_commentaire', methods: ['DELETE'])]
    public function deleteCommentaire($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $commentaire = $entityManager->getRepository(Commentaire::class)->find($id);
        if ($commentaire) {
            $entityManager->remove($commentaire);
            $entityManager->flush();
            return new JsonResponse(["success" => true]);
        }
        return new JsonResponse(["success" => false]);
    }
}
