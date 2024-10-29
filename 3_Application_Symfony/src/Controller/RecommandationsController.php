<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Repository\GamesRepository;
use App\Repository\DashboardRepository;

class RecommandationsController extends AbstractController
{
    #[Route('/recommandations', name: 'app_recommandations')]
    public function index(GamesRepository $games_repository, DashboardRepository $dashboard_repository): Response
    {
        $games = $games_repository->findBy(
            ['review_score' => 90, 'price' => 50]
        );

        $test = $dashboard_repository->findDataSelectedGames();

        //dd($test);
        
        return $this->render('recommandations/index.html.twig', [
            'controller_name' => 'RecommandationsController',
            'games' => $games,
        ]);
    }




    #[Route('/recommandations/ajaxModal', name: 'app_recommandations_ajax_modal')]
    public function ajaxGraphPeriod(GamesRepository $repository, Request $request) : Response
    {
        // Récupération AJAX des données nécéssaires à la construction de la modal.
        $appID = $request->request->get('appID');

        // Appel de la requête du repository
        $dataModal = $repository->findDataGameModal($appID);

        $response = new Response(json_encode($dataModal));
        return $response;
    }


    #[Route('/recommandations/ajaxSelection', name: 'app_recommandations_ajax_selection')]
    public function ajaxSelectionDashboard(DashboardRepository $dashboard_repository, Request $request) : Response
    {
        $appID = $request->request->get('appID');


        // on verifie que l'ID du jeu selectionné n'est pas déjà dans la table Dashboard.
        $dataTableDashboard = $dashboard_repository->findDataSelectedGames();
        //dd($datatableGames["0"]["app_id"]);

        $array_test = array();
        for ($i = 0; $i <= count($dataTableDashboard)-1; $i++) {
            if ($appID === $dataTableDashboard[strval($i)]["app_id"]){
                array_push($array_test, 0);
                dd($array_test);
                break;
            }else{
                array_push($array_test, 1);
            }
        }
        //dd($array_test);

        // Récupération des données du jeu dans la table Games.
        $dataGameInsert = $dashboard_repository->findDataTableGames($appID)["0"];

        //Envoi de ces données dans le repository pour l'insertion.
        $booleen_insertion=$dashboard_repository->insertIntoDashboardTable($dataGameInsert);
        // le booleen est utile pour les test mais les cas d'erreur sont déjà gérés par symfony.
        dd($booleen_insertion);

        $response = new Response(json_encode($appID));
        return $response;
    }

   
}