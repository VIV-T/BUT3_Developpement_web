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

// Filtres & Formulaires
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class RecommandationsController extends AbstractController
{
    #[Route('/recommandations', name: 'app_recommandations')]
    public function index(GamesRepository $games_repository, DashboardRepository $dashboard_repository): Response
    {
        # Test slider range
        
        $slider_range_builder = $this->createFormBuilder()->add('name', RangeType::class, [
            'attr' => [
                'min' => 5,
                'max' => 50
            ],
        ])->add('name2', RangeType::class, [
            'attr' => [
                'min' => 50,
                'max' => 100
            ],
        ])->getForm();




        $games1 = $games_repository->findBy(
            ['PEGI'=>3]
        );
        $games2 = $games_repository->findBy(
            ['PEGI'=>7]
        );

        $games = array_merge($games1, $games2);
        
        //$games = $games_repository->findDataRecommandationPage();
        //dd($games);

        // recuperation des valeur min & max de chacune des 4 slider range.
        // initialisation.
        $min_copiesSold = 0;
        $max_copiesSold = 0;
        $min_revenue = 0;
        $max_revenue = 0;
        $min_reviewScore = 0;
        $max_reviewScore = 0;
        $min_recommandations = 0;
        $max_recommandations = 0;
        foreach ($games as $game) {
            //dd($game);
            // copiesSold
            if ($game->getCopiesSold()>$max_copiesSold){
                $max_copiesSold = $game->getCopiesSold();
            }

            if ($min_copiesSold === 0){
                $min_copiesSold = $game->getCopiesSold();
            }elseif ($game->getCopiesSold()<$min_copiesSold){
                $min_copiesSold = $game->getCopiesSold();
            }


            // revenue
            if ($game->getRevenue()>$max_revenue){
                $max_revenue = $game->getRevenue();
            }

            if ($min_revenue === 0){
                $min_revenue = $game->getRevenue();
            }elseif ($game->getRevenue()<$min_revenue){
                $min_revenue = $game->getRevenue();
            }


            // reviewScore
            if ($game->getReviewScore()>$max_reviewScore){
                $max_reviewScore = $game->getReviewScore();
            }

            if ($min_reviewScore === 0){
                $min_reviewScore = $game->getReviewScore();
            }elseif ($game->getReviewScore()<$min_reviewScore){
                $min_reviewScore = $game->getReviewScore();
            }



            // recommandations
            if ($game->getRecommandations()>$max_recommandations){
                $max_recommandations = $game->getRecommandations();
            }

            if ($min_recommandations === 0){
                $min_recommandations = $game->getRecommandations();
            }elseif ($game->getRecommandations()<$min_recommandations){
                $min_recommandations = $game->getRecommandations();
            }
        }
        
        $res = [
            $min_copiesSold,
            $max_copiesSold,
            $min_revenue,
            $max_revenue,
            $min_reviewScore,
            $max_reviewScore,
            $min_recommandations,
            $max_recommandations
        ];
        //dd($res);
        
        // Truncation of the dashboard table data
        $dashboard_repository->truncateDashboardTable();

        $formGenres = $this->createFormGenres($games_repository);
        $formPublisherClass = $this->createFormPublisherClass($games_repository);
        $formOrderBy= $this->createFormOrderBy($games_repository);

        return $this->render('recommandations/index.html.twig', [
            'controller_name' => 'RecommandationsController',
            'games' => $games,
            'slider_range_builder'=>$slider_range_builder, 
            "res"=>$res, 
            'formGenres' => $formGenres, 
            "formPublisherClass" => $formPublisherClass, 
            "formOrderBy"=> $formOrderBy
        ]);
    }


    public function createFormGenres(GamesRepository $game_repository){

        $query_results = $game_repository->get_genres_list();
        $genres_list =  [];

        foreach ($query_results as $result){
            $genres_list[$result["label"]]=$result["label"];
        }

        $form = $this->createFormBuilder()->add('form_genres', ChoiceType::class, [
            'choices' => $genres_list,
            'data' => array_values($genres_list),
            'multiple' => true,  // Permet de choisir plusieurs options
            'expanded' => true, // Pour afficher les radio buttons
        ])->getForm();
        return $form;
    }


    public function createFormPublisherClass(GamesRepository $game_repository){
        $query_results = $game_repository->get_publisherClass_list();
        $publisherClass_list =  [];

        foreach ($query_results as $result){
            $publisherClass_list[$result["publisher_class"]]=$result["publisher_class"];
        }
        //dd($publisherClass_list);

        $form = $this->createFormBuilder()->add('form_publisher_class', ChoiceType::class, [
            'choices' => $publisherClass_list,
            'data' => array_values($publisherClass_list),
            'multiple' => true,  // Permet de choisir plusieurs options
            'expanded' => true, // Pour afficher les radio buttons
        ])->getForm();
        return $form;
    }


    // Fonction de Rémy 
    public function makeEqualLength_array_str_value(array $tableau_chaine): array 
    {
        $longueur_max = max(array_map('strlen', $tableau_chaine)); // fonction map qui applique
        //la fonction strlen à chaque element du tableau, je recupere que la + grande

        $tableau_chaine_nettoye = []; // initialise tableau

        foreach ($tableau_chaine as $element) {
            $tableau_chaine_nettoye[] = str_pad($element, $longueur_max, ' ');
            // str_pad -> complete par la droite une string par un caractere mentionné (ici ' ')
        }

        return $tableau_chaine_nettoye;
    }


    public function createFormOrderBy(GamesRepository $game_repository){


        $form = $this->createFormBuilder()->add('form_OrderBy', ChoiceType::class, [
            'choices' => [
                "Copies sold"=>"copies_sold", 
                "Revenue"=>"revenue", 
                "Recommandations"=>"recommandations", 
                "Review Score"=>"review_score"
            ],
            'data' => "copies_sold",
            'expanded' => false, // Pour afficher les radio buttons

        ])->getForm();
        return $form;
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
        
        //// Code d'insertion dans la table Dashboard.
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


    // Methode AJAX - filtrage selon les paramétrage de l'utilisateur.
    #[Route('/recommandations/ajaxSubset', name: 'app_recommandations_ajax_subset')]
    public function ajaxSubset(GamesRepository $game_repository, Request $request) : Response
    {
        // Récupération des paramètres de la requête.
        $parameters = json_decode($request->request->get('parameters'));
       
        //dd($parameters);

        // Appel de la requete SQL de récupération des données filtrées
        $data = $game_repository->get_subseted_data($parameters);
    
        $response = new Response(json_encode($data));
        return $response;
    }


    /////
    ///// Page Dashboard
    /////

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
        // recuperation de l'OS
        $os = preg_replace("/(^[\w]+)([A-Za-z0-9 ().-]+$)/", "$1", php_uname()); 
        $cwd = $this->getParameter("dir_script_r"); // la variable d'environement créée précédement.
        
        // Condition sur les path des fichiers et sur la cmd à executer en fonction de l'os (windows ou macOS)
        if ($os === "Windows"){
            $sep="\\";
            $R_cmd = ".\Rscript.exe";
        }else{
            $sep="/";
            $R_cmd = "Rscript";
        }
        // creation des path pour chacun des fichier R a executer :
        $path_dir_r_script_graph = $cwd.$sep."assets".$sep."RGraph".$sep."Dashboard".$sep."creation_graphes_dashboard.R";
        $path_to_graphes_analyis = $cwd.$sep."assets".$sep."RGraph".$sep."Dashboard".$sep."results";
        
        
        // Execution de tous les scripts R
        $process = new Process([$R_cmd, $path_dir_r_script_graph]);
        if ($os === "Windows"){
            $process->setWorkingDirectory("C:/Program Files/R/R-4.4.2/bin/x64");
        }
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
    function set_data_options_barchart_age_Dashboard(DashboardRepository $dashboard_repository) : Array
    {
        // Récupération des données utiles à la construction du graphique
        $dataBarChartAgeDashboard=$dashboard_repository->constructArray_DataBarChartAge()[0];
        
        $data = [
            'labels' => array_values($dataBarChartAgeDashboard['label']),
            'datasets'=> [[
                "label"=>"Number of games",
                "data" => array_values($dataBarChartAgeDashboard['data']),
                'backgroundColor' => 'rgba(20,122,255,0.5)',
                'borderColor' => 'rgba(20,122,255,1)',
                'borderWidth' => 3    
                ]],
            ];
        
        $options = [
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
        ];
    
        return ["data"=>$data, "options"=>$options];
    }


    function createBarChartAgeDashboard(DashboardRepository $dashboard_repository, ChartBuilderInterface $chartBuilder) : Chart
    {
        $data_options_chart = $this-> set_data_options_barchart_age_Dashboard($dashboard_repository);

        // Appel de symfony UX pour créer le chart
        $BarChartAgeDashboard = $chartBuilder->createChart(Chart::TYPE_BAR);
        $BarChartAgeDashboard->setData($data_options_chart["data"]);
        $BarChartAgeDashboard->setOptions($data_options_chart["options"]);
        
        return $BarChartAgeDashboard ;
    }


    // Histogramme - reviewScore
    function set_data_options_barchart_reviewScoreDashboard(DashboardRepository $dashboard_repository) : Array
    {
        // Récupération des données utiles à la construction du graphique
        $dataBarchartReviewScoreDashboard= $dashboard_repository->constructArray_DataBarChartReviewScore()[0];
    
        $data = [
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
        ];
        
        $options = [
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
        ];

        return ["data"=>$data, "options"=>$options];
    }

    function createBarchartReviewScoreDashboard(DashboardRepository $dashboard_repository, ChartBuilderInterface $chartBuilder) : Chart
    {
        // Récupération des données utiles à la construction
        $data_options_chart = $this->set_data_options_barchart_reviewScoreDashboard($dashboard_repository);

        
        // Appel de symfony UX pour créer le chart
        $BarChartReviewScoreDashboard = $chartBuilder->createChart(Chart::TYPE_BAR);
        $BarChartReviewScoreDashboard->setData($data_options_chart["data"]);
        $BarChartReviewScoreDashboard->setOptions($data_options_chart["options"]);
        
        
        return $BarChartReviewScoreDashboard;
    }
}