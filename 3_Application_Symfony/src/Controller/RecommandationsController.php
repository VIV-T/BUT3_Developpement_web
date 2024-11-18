<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Repository\GamesRepository;
use App\Repository\DashboardRepository;

// To make chart with symfony UX - chartjs
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

// execution des scripts R
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class RecommandationsController extends AbstractController
{
    #[Route('/recommandations', name: 'app_recommandations')]
    public function index(GamesRepository $games_repository, DashboardRepository $dashboard_repository): Response
    {
        $games1 = $games_repository->findBy(
            ['PEGI'=>3]
        );
        $games2 = $games_repository->findBy(
            ['PEGI'=>7]
        );

        $games = array_merge($games1, $games2);
        
        //$games = $games_repository->findDataRecommandationPage();
        //dd($games2);

        // Truncation of the dashboard table data
        $dashboard_repository->truncateDashboardTable();

        
        
        return $this->render('recommandations/index.html.twig', [
            'controller_name' => 'RecommandationsController',
            'games' => $games,
        ]);
    }


    // Methode AJAX - requêtage BD pour obtenir les données pour remplir la modal.
    #[Route('/recommandations/ajaxModal', name: 'app_recommandations_ajax_modal')]
    public function ajaxGraphPeriod(GamesRepository $game_repository, Request $request) : Response
    {
        // Récupération AJAX des données nécéssaires à la construction de la modal.
        $appID = $request->request->get('appID');

        // Appel de la requête du repository
        $dataModal = $game_repository->findDataGameModal($appID);

        $response = new Response(json_encode($dataModal));
        return $response;
    }


    // Methode AJAX - quand un jeu est selectionné : on vérifie qu'il n'est pas déjà dans la dashboard table
    // puis on y insere ses données à partir des données de la table games.
    #[Route('/recommandations/ajaxSelect', name: 'app_recommandations_ajax_select')]
    public function ajaxSelectDashboard(DashboardRepository $dashboard_repository, Request $request) : Response
    {
        $appID = $request->request->get('appID');


        // on verifie que l'ID du jeu selectionné n'est pas déjà dans la table Dashboard.
        // on SELECT tous les jeux de la table dashboard
        $dataTableDashboard = $dashboard_repository->findDataSelectedGames();

        // creation d'un observateur bool : si le jeux est présent dans la table => true
        $observateur_presence_game_dashboardTable = true;

        for ($i = 0; $i <= count($dataTableDashboard)-1; $i++) {
            if ($appID === $dataTableDashboard[strval($i)]["app_id"]){
                $observateur_presence_game_dashboardTable = false;
                break;
            }
        }

        // Récupération des données du jeu dans la table Games.
        $dataGameToInsert = $dashboard_repository->findDataTableGames($appID)["0"];

        //Envoi de ces données dans le repository pour l'insertion.
        if ($observateur_presence_game_dashboardTable){
            $dashboard_repository->insertIntoDashboardTable($dataGameToInsert);
        }
        //$booleen_insertion=$dashboard_repository->insertIntoDashboardTable($dataGameToInsert);
        // le booleen est utile pour les test mais les cas d'erreur sont déjà gérés par symfony.
        //dd($booleen_insertion);

        $header_img = $dataGameToInsert["header_img"];
        

        $response = new Response(json_encode($header_img));
        return $response;
    }


    // Methode AJAX - appelée quand un jeu est déselectionné : on supprime ses données de la table dashboard.
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


    // Methode AJAX - compte du nombre de jeux dans la table dashboard.
    #[Route('/recommandations/ajaxCount', name: 'app_recommandations_ajax_count')]
    public function ajaxCountSelectedGames(DashboardRepository $dashboard_repository, Request $request) : Response
    {

        // On compte le nombre de jeux dans la table dashboard
        $account = $dashboard_repository->countSelectedGames()["0"]["account"];
    
        $response = new Response(json_encode($account));
        return $response;
    }


    //Route : Dashboard - quand on click sur le bouton "Continue"
    #[Route('/recommandations/dashboard', name: 'app_recommandations_dashobard')]
    public function index_dashboard(DashboardRepository $dashboard_repository, ChartBuilderInterface $chartBuilder): Response
    {
        $dashboard_games = $dashboard_repository->findAll();

        $dataBarChartAge = $dashboard_repository->constructArray_DataBarChartAge();
        
        // Barchart - Age
        $barChartAge= $this->createBarChartAgeDashboard($dashboard_repository, $chartBuilder);
        //dd($barChartAge);

        // Barchart - ReviewScore
        $barChartReviewScore= $this->createBarchartReviewScoreDashboard($dashboard_repository, $chartBuilder);
        //dd($barChartReviewScore);

        //Top 3 games in the data selection
        $dataTop3 = $dashboard_repository->findDataTopGamesInSelection(); 
        //dd($dataTop3);


        //// execution du script R appliquant des méthodes de DM aux données + création de graphes ggplot2.
        $cwd = $this->getParameter("dir_script_r"); // la variable d'environement créée précédement.
        //dd($cwd);

        // creation des path pour chacun des fichier R a executer :
        $path_dir_r_script_graph = $cwd."\\assets\\RGraph\\Dashboard\\creation_graphes_dashboard.R";
        $path_to_graphes_analyis = $cwd."\\assets\\RGraph\\Dashboard\\results";
        
        
        // Execution de tous les scripts R
        $process = new Process(['.\Rscript.exe', $path_dir_r_script_graph]);
        $process->setWorkingDirectory("C:/Program Files/R/R-4.4.2/bin/x64");
        $process->setTimeout(300);
        $process->run();
        
        


        return $this->render('recommandations/dashboard.html.twig', [
            'controller_name' => 'RecommandationsController',
            'dashboard_games' => $dashboard_games,
            'barChartAge' => $barChartAge,
            'barChartReviewScore'=> $barChartReviewScore,
            'dataTop3'=>$dataTop3
        ]);
    }

    // Bar chart - Age
    function createBarChartAgeDashboard(DashboardRepository $dashboard_repository, ChartBuilderInterface $chartBuilder) : Chart
    {
        // Récupération des données utiles à la construction du graphique
        $dataBarChartAgeDashboard=$dashboard_repository->constructArray_DataBarChartAge()[0];

        // Appel de symfony UX pour créer le chart
        $BarChartAgeDashboard = $chartBuilder->createChart(Chart::TYPE_BAR);
        $BarChartAgeDashboard->setData([
            'labels' => array_values($dataBarChartAgeDashboard['label']),
            'datasets'=> [[
                "label"=>"Number of games",
                "data" => array_values($dataBarChartAgeDashboard['data']),
                'backgroundColor' => 'rgba(20,122,255,0.5)',
                'borderColor' => 'rgba(20,122,255,1)',
                'borderWidth' => 3    
            ]],
        ]);
        $BarChartAgeDashboard->setOptions([
            'scales' => [
                'x'=>[
                    'title'=>[
                        'display'=>true, 
                        'text'=>'PEGI'
                    ]
                ], 
                'y'=>[
                    'title'=>[
                        'display'=>true, 
                        'text'=>'Number of games'
                    ]
                ]    
            ],
        ]);
        
        return $BarChartAgeDashboard;
    }


    // Histogramme - reviewScore
    function createBarchartReviewScoreDashboard(DashboardRepository $dashboard_repository, ChartBuilderInterface $chartBuilder) : Chart
    {
        // Récupération des données utiles à la construction du graphique
        $dataBarchartReviewScoreDashboard= $dashboard_repository->constructArray_DataBarChartReviewScore()[0];

        // Appel de symfony UX pour créer le chart
        $BarChartReviewScoreDashboard = $chartBuilder->createChart(Chart::TYPE_BAR);
        $BarChartReviewScoreDashboard->setData([
            'labels' => array_values($dataBarchartReviewScoreDashboard['label']),
            'datasets'=> [[
                "label"=>"Number of games", 
                "data" => array_values($dataBarchartReviewScoreDashboard['data']),
                'boderWidth'=>1,
                'barPercentage'=>1,
                'categoryPercentage'=>1,
                'backgroundColor' => '#147aff',
                'borderColor' => '#1A2A3F',
                'borderWidth' =>1
            ]],
        ]);
        $BarChartReviewScoreDashboard->setOptions([
            'scales' => [
                'x'=>[
                    'type'=>'linear',
                    'offset'=>false, 
                    'grid'=>[
                        'offset'=>false
                    ], 
                    'title'=>[
                        'display'=>true, 
                        'text'=>'Review Score'
                    ]
                ], 
                'y'=>[
                    'title'=>[
                        'display'=>true, 
                        'text'=>'Number of games'
                    ]
                ]    
            ],
        ]);
        
        
        return $BarChartReviewScoreDashboard;
    }
   
}