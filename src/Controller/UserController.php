<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmployeeType;
use App\Form\EmployerType;
use App\Form\ChangePasswordType;
use App\Form\UserType;
use App\Service\UploaderService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Google\Client;
use Google\Service\RecaptchaEnterprise;
use Symfony\Component\HttpClient\HttpClient;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


#[Route('/user')]
final class UserController extends AbstractController
{
    private SluggerInterface $slugger;
    private UploaderService $uploaderService;

    public function __construct(SluggerInterface $slugger, UploaderService $uploaderService)
    {
        $this->slugger = $slugger;
        $this->uploaderService = $uploaderService;
    }
    private function sendVerificationEmail(User $user, MailerInterface $mailer): void
    {
        $verificationCode = random_int(100000, 999999); // Generate a 6-digit code
        $user->setConfirmationToken($verificationCode);

        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('Verify your email address')
            ->html("Your verification code is: <strong>$verificationCode</strong>");

        $mailer->send($email);
    }

    #[Route('/allusers',name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findBy(['userType' => ['employee','employer']]),
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
    
    private function verifyRecaptcha(string $recaptchaResponse): bool
    {
        $recaptchaSecret = $_ENV['RECAPTCHA_SECRET_KEY'];
        $client = HttpClient::create();
        
        $response = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $recaptchaSecret,
                'response' => $recaptchaResponse
            ]
        ]);

        $data = $response->toArray();
        return $data['success'] ?? false;
    }

    private function handleRegistration(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer,
        string $userType,
        string $formType
    ): Response {
        $recaptchaSiteKey = $_ENV['GOOGLE_RECAPTCHA_SITE_KEY'] ?? null;
    
        $user = new User();
        $user->setUserType($userType);
    
        $form = $this->createForm($formType, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword && strlen($plainPassword) >= 8 && strlen($plainPassword) <= 20) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            } else {
                $this->addFlash('error', 'Password must be between 8 and 20 characters.');
                return $this->redirectToRoute("app_{$userType}_register");
            }
    
            $this->sendVerificationEmail($user, $mailer);
    
            $entityManager->persist($user);
            $entityManager->flush();
    
            $this->addFlash('success', 'A verification code has been sent to your email.');
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render("user/{$userType}_register.html.twig", [
            'form' => $form->createView(),
            'google_recaptcha_site_key' => $recaptchaSiteKey,
        ]);
    }
    
#[Route('/register/employee', name: 'app_employee_register', methods: ['GET', 'POST'])]
public function registerEmployee(
    Request $request,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher,
    MailerInterface $mailer
): Response {
    return $this->handleRegistration($request, $entityManager, $passwordHasher, $mailer, 'employee', EmployeeType::class);
}

#[Route('/register/employer', name: 'app_employer_register', methods: ['GET', 'POST'])]
public function registerEmployer(
    Request $request,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher,
    MailerInterface $mailer
): Response {
    return $this->handleRegistration($request, $entityManager, $passwordHasher, $mailer, 'employer', EmployerType::class);
}



    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/employee/{id}/edit', name: 'app_employee_edit', methods: ['GET', 'POST'])]
public function editEmployee(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
{
    if ($user->getUserType() !== 'employee' && $user->getUserType() !== 'admin') {
        throw $this->createAccessDeniedException('Accès refusé.');
    }

    $form = $this->createForm(EmployeeType::class, $user);
    $form->handleRequest($request);

    $previousImage = $user->getImage();

    if ($form->isSubmitted() && $form->isValid()) {
        // Mise à jour du mot de passe uniquement si un nouveau est fourni
        $plainPassword = $form->get('password')->getData();
        if (!empty($plainPassword)) {
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        }

        // Gestion de l'image
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $imageFilename = $this->uploaderService->upload($imageFile);
            $user->setImage($imageFilename);
        } else {
            $user->setImage($previousImage);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Les modifications ont été enregistrées avec succès.');
        return $this->redirectToRoute('app_employee_edit', ['id' => $user->getId()]);        
    }

    return $this->render('user/employee_edit.html.twig', [
        'user' => $user,
        'form' => $form->createView(),
    ]);
}
#[Route('/{id}/change-password', name: 'app_user_change_password', methods: ['GET', 'POST'])]
public function changePassword(Request $request, User $user, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
{
    // Ensure the logged-in user is the same as the user being edited
    if ($user !== $this->getUser()) {
        throw $this->createAccessDeniedException('You are not allowed to change this password.');
    }

    $form = $this->createForm(ChangePasswordType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $currentPassword = $form->get('currentPassword')->getData();
        $newPassword = $form->get('newPassword')->getData();

        // Verify current password
        if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
            $this->addFlash('error', 'Current password is incorrect.');
            return $this->redirectToRoute('app_user_change_password', ['id' => $user->getId()]);
        }

        // Hash and set the new password
        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Password changed successfully.');

        return $this->redirectToRoute('app_user_index');
    }

    return $this->render('user/change_password.html.twig', [
        'form' => $form->createView(),
    ]);
}


#[Route('/employer/{id}/edit', name: 'app_employer_edit', methods: ['GET', 'POST'])]
public function editEmployer(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
{
    if ($user->getUserType() !== 'employer' && $user->getUserType() !== 'admin') {
        throw $this->createAccessDeniedException('Accès refusé.');
    }

    $form = $this->createForm(EmployerType::class, $user);
    $form->handleRequest($request);

    $previousImage = $user->getImage();

    if ($form->isSubmitted() && $form->isValid()) {
        // Mise à jour du mot de passe uniquement si un nouveau est fourni
        $plainPassword = $form->get('password')->getData();
        if (!empty($plainPassword)) {
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        }

        // Gestion de l'image
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $imageFilename = $this->uploaderService->upload($imageFile);
            $user->setImage($imageFilename);
        } else {
            $user->setImage($previousImage);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Les modifications ont été enregistrées avec succès.');
        return $this->redirectToRoute('app_employer_edit', ['id' => $user->getId()]);
    }

    return $this->render('user/employer_edit.html.twig', [
        'user' => $user,
        'form' => $form->createView(),
    ]);
}

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
    
        $previousImage = $user->getImage();
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour du mot de passe uniquement si un nouveau est fourni
            $plainPassword = $form->get('password')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }
    
            // Mise à jour de l'image si un nouveau fichier est uploadé
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageFilename = $this->uploaderService->upload($imageFile);
                $user->setImage($imageFilename);
            } else {
                $user->setImage($previousImage);
            }
    
            $entityManager->persist($user);
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