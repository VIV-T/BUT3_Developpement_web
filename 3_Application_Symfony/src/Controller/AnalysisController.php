<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

// To get data with SQL query in the repository
use App\Repository\GamesRepository;

// To make chart with symfony UX - chartjs
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

//Formulaires - utile ici pour les radio-button par exemple
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

// execution des scripts R
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AnalysisController extends AbstractController
{
    ///// Fonctionnement : seule la méthode préfixée par "#[Route('/analysis', name: 'app_analysis')]"
    //                      sera appelée lors du lancement de la page. Le reste des méthode de classe
    //                      doivent être appelée à l'intérieur de cette méthode principale.

    #[Route('/analysis', name: 'app_analysis')]
    public function index(GamesRepository $repository, ChartBuilderInterface $chartBuilder, Request $request): Response
    {
        // Avant de renvoyer tous les objets créés et nécessaire dans notre template, il faut les initialiser/créer.

        ///// Premier graphique - GraphFiveDim
        /////
        // Fonction de création du graphique + des données associées
        $arrayGraphFiveDim = $this->construcGraphFiveDim($repository, $chartBuilder);

        $queryGraphFiveDim = $arrayGraphFiveDim[0];
        $chartFiveDim = $arrayGraphFiveDim[1];


        ///// Second et Troisieme graphiques - Graph Year and Month
        /////
        
        // La recupération de la valeur du formulaire pour la contruction du graph
        // et la construction en querstion se font dans la requête Ajax.        
        $chart_month = $this->createGraphPeriod($repository, $chartBuilder, "month");
        $chart_year = $this->createGraphPeriod($repository, $chartBuilder, "year");
        //dd($chart_month);

        //// execution du script R appliquant des méthodes de DM aux données + création de graphes ggplot2.

        // recuperation de l'OS
        $os = preg_replace("/(^[\w]+)([A-Za-z0-9 ().-]+$)/", "$1", php_uname()); 
        $cwd = $this->getParameter("dir_script_r"); // la variable d'environement créée précédement.
        //dd($cwd);
        
        // Si execution des scripts contenus dans les directory d'assets de l'application :
        // Erreur d'execution : code -1073741819 => probleme de permission pour executer la cmd depuis symfony
        // Conclusion : les deux facon de faire : exec/Process ne sont pas un probleme car elles renvoient la mm erreur.

        // Condition sur les path des fichiers et sur la cmd à executer en fonction de l'os (windows ou macOS)
        if ($os === "Windows"){
            $sep="\\";
            $R_cmd = ".\Rscript.exe";
        }else{
            $sep="/";
            $R_cmd = "Rscript";
        }
        // creation des path pour chacun des fichier R a executer :
        $path_dir_r_script_acp = $cwd.$sep."assets".$sep."RGraph".$sep."Analysis".$sep."creation_graphes_ACP_STEAM.R";
        $path_dir_r_script_graph = $cwd.$sep."assets".$sep."RGraph".$sep."Analysis".$sep."creation_graphes_analysis.R";
        $liste_scripts = [$path_dir_r_script_acp, $path_dir_r_script_graph];
        $path_to_graphes_analyis = $cwd.$sep."assets".$sep."RGraph".$sep."Analysis".$sep."results";
        
        
        // Execution de tous les scripts R
        /*foreach($liste_scripts as $r_script){
            $process = new Process([$R_cmd, $r_script]);
            if ($os === "Windows"){
                $process->setWorkingDirectory("C:/Program Files/R/R-4.4.2/bin/x64");
            }
            $process->setTimeout(300);
            $process->run();
        }*/
        // deboggage
        //$process->run(function ($type, $buffer) {
        //    if (Process::ERR === $type) {
        //        //dd('ERR > '.$buffer);
        //    } else {
        //        //dd( 'OUT > '.$buffer);
        //    }
        //});


        
        ///// Renvoie tous les objets dans le template twig associé.
        return $this->render('analysis/index.html.twig', [
            'controller_name' => 'AnalysisController',
            'viewGraphFiveDim' => $queryGraphFiveDim,
            'chartFiveDim' => $chartFiveDim,
            'path_to_graphes_analyis'=> $path_to_graphes_analyis,
            "chart_month" => $chart_month,
            "chart_year" => $chart_year
        ]);
    }

    
    ///// Premier graphique - GraphFiveDim
    /////
    // création du graphe
    public function construcGraphFiveDim(GamesRepository $repository, ChartBuilderInterface $chartBuilder): Array
    {
        $queryGraphFiveDim = $repository->findDataGraphFiveDim();
        $dataGraphFiveDim = $repository->constructArray_DataGraphFiveDim();

        // Pour comprendre ce qu'il y  a dans cet objet $dataGraphFiveDim
        //dd(json_encode($dataGraphFiveDim));
        //dd($dataGraphFiveDim);
        //dd($queryGraphFiveDim[0]['sommeCopiesSold']);
        //dd($queryGraphFiveDim[0]['label']);
        //dd($queryGraphFiveDim[0]['nbGames']);

        $chartFiveDim = $chartBuilder->createChart(Chart::TYPE_BUBBLE);
        $chartFiveDim->setData([
            'datasets'=>$dataGraphFiveDim,
        ]);
        $chartFiveDim->setOptions([
            "scales"=>[
                "x"=>[
                    "title"=>[
                        'display' => true,
                        'text' => "Revenue (Billions $)"
                    ]
                ],
                "y"=>[
                    "title"=>[
                        'display' => true,
                        'text' => "Copies Sold (Millions)"
                    ]
                ],
            ]
        ]);

        return array($queryGraphFiveDim, $chartFiveDim);
    }


    ///// Second graphique - GraphYearGenre
    /////

    // creation du graph a partir des données formatées dans le repository.
    function createGraphPeriod(GamesRepository $repository, ChartBuilderInterface $chartBuilder, $period) : Chart
    {
        // Appel de la méthode qui renvoie les données et options relatives au graphique
        // On met ensuite ici en forme les données pour pouvoir utiliser symfony UX dans la methode de creation de chart
        $array_data_options = $repository->constructArray_DataGraphYearGenre($period);
        
        // Appel de symfony UX pour créer le chart
        $LineChartPeriod = $chartBuilder->createChart(Chart::TYPE_LINE);
        $LineChartPeriod->setData($array_data_options["data"]);
        $LineChartPeriod->setOptions($array_data_options["options"]);
        
        return $LineChartPeriod ;
    }
}