<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmployeeType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/user')]
final class UserController extends AbstractController
{
    private SluggerInterface $slugger;
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/employees', name: 'app_user_employees', methods: ['GET'])]
    public function employees(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findBy(['userType' => 'employee']),
            'userType' => 'Employees'
        ]);
    }

    #[Route('/employers', name: 'app_user_employers', methods: ['GET'])]
    public function employers(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findBy(['userType' => 'employer']),
            'userType' => 'Employers'
        ]);
    }

    #[Route('/register/employee', name: 'app_employee_register', methods: ['GET', 'POST'])]
public function registerEmployee(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
{
    $user = new User();
    $user->setUserType('employee');

    // Create the form
    $form = $this->createForm(EmployeeType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Handle password
        $plainPassword = $form->get('password')->getData();
        if ($plainPassword && strlen($plainPassword) >= 8 && strlen($plainPassword) <= 20) {
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        } else {
            $this->addFlash('error', 'Password must be between 8 and 20 characters.');
            return $this->redirectToRoute('app_employee_register');
        }

        // Handle image upload (if applicable)
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            // Use the slugger to sanitize the filename
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename); // Use $this->slugger
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                $user->setImage($newFilename); // Set the new image filename on the user
            } catch (FileException $e) {
                // Handle exception (optional)
                $this->addFlash('error', 'An error occurred while uploading the image.');
                return $this->redirectToRoute('app_employee_register');
            }
        }

        // Persist the new employee user to the database
        $entityManager->persist($user);
        $entityManager->flush();

        // Redirect to the user index page
        return $this->redirectToRoute('app_user_index');
    }

    // Render the form view
    return $this->render('user/employee_register.html.twig', [
        'form' => $form->createView(),
    ]);
}


    #[Route('/register/employer', name: 'app_employer_register', methods: ['GET', 'POST'])]
public function registerEmployer(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
{
    $user = new User();
    $user->setUserType('employer');

    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $plainPassword = $form->get('password')->getData();
        if ($plainPassword && strlen($plainPassword) >= 8 && strlen($plainPassword) <= 20) {
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        } else {
            $this->addFlash('error', 'Password must be between 8 and 20 characters.');
            return $this->redirectToRoute('app_employer_register');
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_user_index');
    }

    return $this->render('user/employer_register.html.twig', [
        'form' => $form->createView(),
    ]);
}


    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UploaderService $uploaderService): Response
{
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    // Store the previous image for reference in case we need to revert.
    $previousImage = $user->getImage(); 

    if ($form->isSubmitted() && $form->isValid()) {
        // Handle password change
        $plainPassword = $form->get('password')->getData();
        if ($plainPassword) {
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }

        // Handle image upload if a new image is provided
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            // Use a service or directly handle file uploads here
            $imageFilename = $uploaderService->upload($imageFile);
            $user->setImage($imageFilename); // Update the entity with the new image filename
        } else {
            // If no new image, retain the old one
            $user->setImage($previousImage);
        }

        $entityManager->flush();
        return $this->redirectToRoute('app_user_index');
    }

    return $this->render('user/edit.html.twig', [
        'user' => $user,
        'form' => $form->createView(),
    ]);
}


    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index');
    }
}
