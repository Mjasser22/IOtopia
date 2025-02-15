<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CommentController extends AbstractController
{
    #[Route('/comment/create', name: 'app_comment_create', methods: ['POST'])]
public function create(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $content = trim($data['content'] ?? '');
    $postId = trim($data['post_id'] ?? '');
    
    if (empty($content)) {
        return new JsonResponse([
            'success' => false,
            'message' => 'Le commentaire ne peut pas être vide.'
        ], 400);
    }

    if (strlen($content) < 3 || strlen($content) > 255) {
        return new JsonResponse([
            'success' => false,
            'message' => 'Le commentaire  doit contenir au minimum 3 caractères et ne peut pas dépasser  255 caractères.'
        ], 400);
    }

    $staticUserId = 4; // ID utilisateur statique pour le test
    $user = $entityManager->getRepository(User::class)->find($staticUserId);
    $post = $postRepository->find($postId);

    if (!$post || !$user) {
        return new JsonResponse([
            'success' => false,
            'message' => 'Post ou utilisateur invalide.'
        ], 400);
    }

    $comment = new Comment();
    $comment->setContent($content);
    $comment->setCreatedAt(new \DateTimeImmutable());
    $comment->setUser($user);
    $comment->setPost($post);

    $entityManager->persist($comment);
    $entityManager->flush();

    return new JsonResponse([
        'success' => true,
        'message' => 'Commentaire ajouté avec succès !',
        'comment' => [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'created_at' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
            'user' => $comment->getUser()->getId()
        ]
    ]);
}

#[Route('/comment/delete/{id}', name: 'app_comment_delete', methods: ['POST'])]
public function delete(Comment $comment, EntityManagerInterface $entityManager): JsonResponse
{
    $entityManager->remove($comment);
    $entityManager->flush();

    return new JsonResponse([
        'success' => true,
        'message' => 'Commentaire supprimé.'
    ]);
}


    #[Route('/comment/edit/{id}', name: 'app_comment_edit', methods: ['POST'])]
    public function edit(Request $request, $id, EntityManagerInterface $entityManager, CommentRepository $commentRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $newContent = trim($data['content'] ?? '');
    
        // Vérifier si le commentaire existe
        $comment = $commentRepository->find($id);
        if (!$comment) {
            return new JsonResponse(['success' => false, 'message' => 'Commentaire introuvable.'], 404);
        }
    
        if (empty($newContent)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Le commentaire ne peut pas être vide.'
            ], 400);
        }
    
        if (strlen($newContent) < 3 || strlen($newContent) > 255) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Le commentaire  doit contenir au minimum 3 caractères et ne peut pas dépasser  255 caractères.'
            ], 400);
        }
    
        // Mettre à jour et enregistrer le commentaire
        $comment->setContent($newContent);
        $entityManager->flush();
    
        return new JsonResponse(['success' => true, 'message' => 'Commentaire mis à jour.', 'newContent' => $newContent]);
    }
    
    
}
