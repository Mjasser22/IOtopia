<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class soinAdminController extends AbstractController
{
    #[Route('/admin/soin/admin', name: 'app_admin_soin_admin')]
    public function index(): Response
    {
        return $this->render('admin/soin_admin/index.html.twig', [
            'controller_name' => 'SoinAdminController',
        ]);
    }

    #[Route('/admin/soin/redirect', name: 'app_admin_soin_redirect')]
    public function redirectToHome(): Response
    {
        return $this->redirectToRoute('app_homepage'); // Ensure 'app_homepage' exists in your routes
    }
}
