<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecommandationsController extends AbstractController
{
    #[Route('/recommandations', name: 'app_recommandations')]
    public function index(): Response
    {
        return $this->render('recommandations/index.html.twig', [
            'controller_name' => 'RecommandationsController',
        ]);
    }
}
