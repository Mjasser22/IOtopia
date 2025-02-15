<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

//#[Route('/post')]
final class PostController extends AbstractController
{
    #[Route('/post/back', name: 'app_post_back__index', methods: ['GET'])]
public function indexback(
    Request $request,
    PostRepository $postRepository,
    CommentRepository $commentRepository,
    EntityManagerInterface $entityManager,
    UserRepository $userRepository
): Response {
    $staticUserId = 3; // ID statique pour le test
    $user = $userRepository->find($staticUserId);

    // Vérification si l'utilisateur existe et s'il a le rôle ROLE_ADMIN
    $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles(), true);

    $posts = $postRepository->findAll();

    // Ajouter la propriété `isItYours` et les commentaires à chaque post
    $postsWithOwnership = array_map(function ($post) use ($staticUserId, $commentRepository) {
        $comments = $commentRepository->findBy(['post' => $post]);
        return [
            'id' => $post->getId(),
            'user' => $post->getUser(),
            'title' => $post->getTitle(),
            'description' => $post->getDescription(),
            'createdAt' => $post->getCreatedAt(),
            'image' => $post->getImage(),
            'isItYours' => $post->getUser() && $post->getUser()->getId() === $staticUserId,
            'comments' => array_map(function ($comment) use ($staticUserId) {
                return [
                    'id' => $comment->getId(),
                    'content' => $comment->getContent(),
                    'createdAt' => $comment->getCreatedAt(),
                    'user' => $comment->getUser(),
                    'isMine' => $comment->getUser() && $comment->getUser()->getId() === $staticUserId,
                ];
            }, $comments)
        ];
    }, $posts);

    return $this->render('back/blog/blog.html.twig', [
        'posts' => $postsWithOwnership,
        'isAdmin' => $isAdmin, // Ajout du booléen pour Twig
    ]);
}

#[Route('back/{id}/edit', name: 'post_edit_back', methods: ['GET', 'POST'])]
public function editback(Request $request, Post $post, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(PostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('imageFile')->getData();

        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            $imageFile->move(
                $this->getParameter('post_images_directory'),
                $newFilename
            );

            $post->setImage($newFilename);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_post_back__index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('back/blog/edit.html.twig', [
        'post' => $post,
        'form' => $form->createView(),
    ]);
}

#[Route('back/{id}/delete', name: 'post_delete_back', methods: ['POST'])]
public function deleteback(Post $post, EntityManagerInterface $entityManager, Request $request): Response
{
    if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
        $entityManager->remove($post);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_post_back__index');
}

#[Route('back/new', name: 'app_post_new_back', methods: ['GET', 'POST'])]
public function newBack(Request $request, EntityManagerInterface $entityManager,UserRepository $userRepository): Response
{
    $post = new Post();
    $form = $this->createForm(PostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('imageFile')->getData();

        if ($imageFile) {
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('post_images_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                throw new \Exception('Erreur lors du téléchargement de l\'image');
            }

            $post->setImage($newFilename);
        }

        $user = $userRepository->find(2);
        if ($user) {
            $post->setUser($user);
        }

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->redirectToRoute('app_post_back__index');
    }

    return $this->render('back/blog/new.html.twig', [
        'post' => $post,
        'form' => $form->createView(),
    ]);
}


    
    #[Route('/post', name: 'app_post_index', methods: ['GET', 'POST'])]
public function index(
    Request $request,
    PostRepository $postRepository,
    CommentRepository $commentRepository,
    EntityManagerInterface $entityManager
): Response {
    $staticUserId = 4; // ID statique pour le test
    $posts = $postRepository->findAll();

    // Ajouter la propriété `isItYours` et les commentaires à chaque post
    $postsWithOwnership = array_map(function ($post) use ($staticUserId, $commentRepository) {
        $comments = $commentRepository->findBy(['post' => $post]);
        return [
            'id' => $post->getId(),
            'user' => $post->getUser(),
            'title' => $post->getTitle(),
            'description' => $post->getDescription(),
            'createdAt' => $post->getCreatedAt(),
            'image' => $post->getImage(),
            'isItYours' => $post->getUser() && $post->getUser()->getId() === $staticUserId,
            'comments' => array_map(function ($comment) use ($staticUserId) {
                return [
                    'id' => $comment->getId(),
                    'content' => $comment->getContent(),
                    'createdAt' => $comment->getCreatedAt(),
                    'user' => $comment->getUser(),
                    'isMine' => $comment->getUser() && $comment->getUser()->getId() === $staticUserId,
                ];
            }, $comments)
        ];
    }, $posts);

   
    return $this->render('post/index.html.twig', [
        'posts' => $postsWithOwnership,
    ]);
}


    

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,UserRepository $userRepository): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('post_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('Erreur lors du téléchargement de l\'image');
                }

                $post->setImage($newFilename);
            }

            $user = $userRepository->find(2);
            if ($user) {
                $post->setUser($user);
            }

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('post_images_directory'),
                    $newFilename
                );

                $post->setImage($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index');
    }
}
