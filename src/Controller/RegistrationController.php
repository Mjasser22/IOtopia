<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($user); // Shows user data in the Symfony debug toolbar
            die(); // Stops execution to see output
            // Hash the password
            $plainPassword = $form->get('plainPassword')->getData();
            dump($plainPassword); // Check if the password is retrieved
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            dump($hashedPassword); // Check if password is properly hashed
            $user->setPassword($hashedPassword);
            die();
            
            // Assign roles based on userType
            $userType = $form->get('userType')->getData();
            if ($userType === 'employer') {
                $user->setRoles(['ROLE_USER', 'ROLE_EMPLOYER']);
            } else {
                $user->setRoles(['ROLE_USER', 'ROLE_EMPLOYEE']);
            }
            $user->setUserType($userType);

            // Generate a confirmation token
            $token = bin2hex(random_bytes(32));
            $user->setConfirmationToken($token);
            if (!$form->isSubmitted()) {
                dd("Form not submitted!");
            }
            if (!$form->isValid()) {
                dd("Form is invalid!", $form->getErrors(true, false));
            }
            // Save user to database
            $entityManager->persist($user);
            dd("User persisted but not flushed!", $user);
            $entityManager->flush();
            dd("User should be in the database now!");

        
            // Send confirmation email
            $confirmationUrl = $this->generateUrl('app_register', ['token' => $token], 0);
            $emailMessage = (new Email())
                ->from('no-reply@yourdomain.com')
                ->to($user->getEmail())
                ->subject('Registration Confirmation')
                ->html('<p>Please confirm your registration by clicking the link below.</p><a href="' . $confirmationUrl . '">Confirm Registration</a>');

            $mailer->send($emailMessage);

            // Redirect with success message
            $this->addFlash('success', 'Registration successful! Check your email for confirmation.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
