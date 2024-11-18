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


        ///// Second graphique - GraphYearGenre
        /////
        // creation du fromulaire 
        $form = $this->createFormPeriod();
        
        // La recupération de la valeur du formulaire pour la contruction du graph
        // et la construction en querstion se font dans la requête Ajax.        

        
        //// execution du script R appliquant des méthodes de DM aux données
        //chdir("C:/Program Files/R/R-4.4.2/bin/x64");
        //exec(".\Rscript.exe C:/Users/TV/Documents/Thib/Metz/Etudes/BUT_3/dvp_web/ProjetSteam/3_Application_Symfony/assets/RGraph/creation_graphes_ACP_STEAM.R");
        
        $cwd = $this->getParameter("dir_script_r"); // la variable d'environement créée précédement.
        //dd($cwd);
        
        //// Execution des script stocké dans un directory annexe.
        
        // Si execution des scripts contenus dans les directory d'assets de l'application :
        // Erreur d'execution : code -1073741819 => probleme de permission pour executer la cmd depuis symfony
        // Conclusion : les deux facon de faire : exec/Process ne sont pas un probleme car elles renvoient la mm erreur.


        // creation des path pour chacun des fichier R a executer :
        //$path_main_dir = preg_replace("(\\\[1-9a-zA-Z_]+$)", "", $cwd);
        //$path_dir_r_script_acp = $path_main_dir."\\scriptR_application\\creation_graphes_ACP_STEAM.R";
        //$path_dir_r_script_graph = $path_main_dir."\\scriptR_application\\creation_graphes_analysis.R";
        //$path_to_graphes_analyis = $path_main_dir."\\scriptR_application\\results\\analysis";
        $path_dir_r_script_acp = $cwd."\\assets\\RGraph\\Analysis\\creation_graphes_ACP_STEAM.R";
        $path_dir_r_script_graph = $cwd."\\assets\\RGraph\\Analysis\\creation_graphes_analysis.R";
        $liste_scripts = [$path_dir_r_script_acp, $path_dir_r_script_graph];
        $path_to_graphes_analyis = $cwd."\\assets\\RGraph\\Analysis\\results";
        
        
        // Execution de tous les scripts R
        foreach($liste_scripts as $r_script){
            $process = new Process(['.\Rscript.exe', $r_script]);
            $process->setWorkingDirectory("C:/Program Files/R/R-4.4.2/bin/x64");
            $process->setTimeout(300);
            $process->run();
        }
        
        //// Méthode 1 : utilisation de exec()
        // $cmd = ".\Rscript.exe ".$dir_script_r_bis;
        // //dd($cmd);
        // chdir("C:/Program Files/R/R-4.4.2/bin/x64");
        // exec($cmd, $output, $retval);
        // dd([$output, $retval]);


        //// Méthode 2 : utilisation de Process()
        //$process = new Process(['.\Rscript.exe', $path_dir_r_script_graph]);
        //$process->setWorkingDirectory("C:/Program Files/R/R-4.4.2/bin/x64");
        //$process->setTimeout(300);
        //$process->run();
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
            'form' => $form,
            'path_to_graphes_analyis'=> $path_to_graphes_analyis,
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

    //création du formulaire pour le choix des perido - radio button
    // modifier ici. - rajouter les paramètre visuels (ex: id ou classe html ?)
    public function createFormPeriod(){
        $form = $this->createFormBuilder()->add('testForm', ChoiceType::class, [
            'choices' => [
                'Year' => 'year',
                'Month' => 'month',
            ],
            'data' => 'year',
            'expanded' => true, // Pour afficher les radio buttons
        ])->getForm();
        return $form;
    }



    #[Route('/analysis/ajaxGraphPeriod', name: 'app_analysis_ajax_graph_period')]
    public function ajaxGraphPeriod(GamesRepository $repository, ChartBuilderInterface $chartBuilder, Request $request) : Response
    {
        // Récupération AJAX des données nécéssaires à la construction du graphique.
        $period = $request->request->get('period');
        
        if (is_null($period)){
            $period = 1;
        }

        // Appel de la méthode qui renvoie les données et options relatives au graphique
        // il est ensuite créé dans le JS à partir de ces données.
        // note probleme de legende des axes + regler les couleurs (ici : valeurs aléatoires)
        $dataGraphYearGenre = $repository->constructArray_DataGraphYearGenre($period);
        

        $response = new Response(json_encode($dataGraphYearGenre));
        return $response;
    }
}