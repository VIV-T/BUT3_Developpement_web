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

        $dashboard_repository->truncateDashboardTable();

        
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


    #[Route('/recommandations/ajaxSelect', name: 'app_recommandations_ajax_select')]
    public function ajaxSelectDashboard(DashboardRepository $dashboard_repository, Request $request) : Response
    {
        $appID = $request->request->get('appID');


        // on verifie que l'ID du jeu selectionné n'est pas déjà dans la table Dashboard.
        // on SELECT tous les jeux de la table dashboard
        $dataTableDashboard = $dashboard_repository->findDataSelectedGames();
        //dd($datatableGames["0"]["app_id"]);

        // creation d'un observateur bool : si le jeux est présent dans la table => true
        $observateur_presence_game_dashboardTable = false;

        for ($i = 0; $i <= count($dataTableDashboard)-1; $i++) {
            if ($appID === $dataTableDashboard[strval($i)]["app_id"]){
                $observateur_presence_game_dashboardTable = true;
                break;
            }
        }

        // Récupération des données du jeu dans la table Games.
        $dataGameToInsert = $dashboard_repository->findDataTableGames($appID)["0"];

        //Envoi de ces données dans le repository pour l'insertion.
        
        $dashboard_repository->insertIntoDashboardTable($dataGameToInsert);
        //$booleen_insertion=$dashboard_repository->insertIntoDashboardTable($dataGameToInsert);
        // le booleen est utile pour les test mais les cas d'erreur sont déjà gérés par symfony.
        //dd($booleen_insertion);
        $header_img = $dataGameToInsert["header_img"];
        

        $response = new Response(json_encode($header_img));
        return $response;
    }



    #[Route('/recommandations/ajaxUnselect', name: 'app_recommandations_ajax_unselect')]
    public function ajaxUnselectDashboard(DashboardRepository $dashboard_repository, Request $request) : Response
    {
        $appID = $request->request->get('appID');

        //suppression des données du jeu dans la table dashboard
        $dashboard_repository->deleteGameFromDashboardTable($appID);

        // Récupération des données du jeu dans la table Games. => pour l'affichage
        $arrayDataGame = $dashboard_repository->findDataTableGames($appID)["0"];
    
        $response = new Response(json_encode($arrayDataGame));
        return $response;
    }


    #[Route('/recommandations/ajaxCount', name: 'app_recommandations_ajax_count')]
    public function ajaxCountSelectedGames(DashboardRepository $dashboard_repository, Request $request) : Response
    {

        // On compte le nombre de jeux dans la table dashboard
        $account = $dashboard_repository->countSelectedGames()["0"]["account"];
    
        $response = new Response(json_encode($account));
        return $response;
    }


    //Route : Dashboard
    #[Route('/recommandations/dashboard', name: 'app_recommandations_dashobard')]
    public function index_dashboard(DashboardRepository $dashboard_repository): Response
    {
        return $this->render('recommandations/dashboard.html.twig', [
            'controller_name' => 'RecommandationsController',
        ]);
    }

   
}