<?php

// src/Controller/ContactController.php
namespace App\Controller;

use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        // Create the form
        $form = $this->createForm(ContactType::class);

        // Handle the request when the form is submitted
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Extract form data
            $fullName = $data['fullName'];
            $email = $data['email'];
            $phone = $data['phone'];
            $message = $data['message'];

            // Send email
            $emailMessage = (new Email())
                ->from($email) // from user email
                ->to('sabeel.agtn@gmail.com') // your email
                ->subject('New Contact Message')
                ->text("Message from: $fullName\nPhone: $phone\n\n$message");

            $mailer->send($emailMessage);

            // Optionally add a success message to the user
            $this->addFlash('success', 'Your message has been sent!');

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
