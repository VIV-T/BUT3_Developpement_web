<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\GamesRepository;

class RecommandationsController extends AbstractController
{
    #[Route('/recommandations', name: 'app_recommandations')]
    public function index(GamesRepository $repository): Response
    {
        $games = $repository->findBy(
            ['review_score' => 90, 'price' => 50]
        );
        
        return $this->render('recommandations/index.html.twig', [
            'controller_name' => 'RecommandationsController',
            'games' => $games,
        ]);
    }
}