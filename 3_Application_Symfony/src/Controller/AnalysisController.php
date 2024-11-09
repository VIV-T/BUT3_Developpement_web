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


        ///// Renvoie tous les objets dans le template twig associé.
        return $this->render('analysis/index.html.twig', [
            'controller_name' => 'AnalysisController',
            'viewGraphFiveDim' => $queryGraphFiveDim,
            'chartFiveDim' => $chartFiveDim,
            'form' => $form,
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