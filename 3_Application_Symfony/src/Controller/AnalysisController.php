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
        // creation du fromulaire et recupération de sa valeur pour la contruction du graph
        $form = $this->createFormPeriod();
        $period = $form['testForm']->getData();


        // Fonction de création du graphique + des données associées
        $arrayGraphYearGenre = $this->construcGraphYearGenre($repository, $chartBuilder, $period);

        $queryGraphYearGenre = $arrayGraphYearGenre[0];
        $chartYearGenre = $arrayGraphYearGenre[1];

        /// /!\ ne marche pas
        // gestion du cas d'interaction de l'utilisateur - click sur le radio button
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $period = $form['testForm']->getData();

            $arrayGraphYearGenre = $this->construcGraphYearGenre($repository, $chartBuilder, $period);

            $queryGraphYearGenre = $arrayGraphYearGenre[0];
            $chartYearGenre = $arrayGraphYearGenre[1];

            return $this->render('analysis/index.html.twig', [
                'controller_name' => 'AnalysisController',
                'viewGraphFiveDim' => $queryGraphFiveDim,
                'chartFiveDim' => $chartFiveDim,
                'viewGraphYearGenre' => $queryGraphYearGenre,
                'chartYearGenres' => $chartYearGenre,
                'form' => $form,
            ]);
        }else{
            $period = $form['testForm']->getData();

            // Fonction de création du graphique + des données associées
            $arrayGraphYearGenre = $this->construcGraphYearGenre($repository, $chartBuilder, $period);

            $queryGraphYearGenre = $arrayGraphYearGenre[0];
            $chartYearGenre = $arrayGraphYearGenre[1];
        }


        ///// Renvoie tous les objets dans le template twig associé.
        return $this->render('analysis/index.html.twig', [
            'controller_name' => 'AnalysisController',
            'viewGraphFiveDim' => $queryGraphFiveDim,
            'chartFiveDim' => $chartFiveDim,
            'viewGraphYearGenre' => $queryGraphYearGenre,
            'chartYearGenres' => $chartYearGenre,
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

        return array($queryGraphFiveDim, $chartFiveDim);
    }


    ///// Second graphique - GraphYearGenre
    /////
    /// Stade expérimental :
    // création du graphe
    public function construcGraphYearGenre(GamesRepository $repository, ChartBuilderInterface $chartBuilder, $period): Array
    {
        $queryGraphYearGenre= $repository->findData_Period($period);
        $dataGraphYearGenre = $repository->constructArray_DataGraphYearGenre($period);

        $chartYearGenre = $chartBuilder->createChart(Chart::TYPE_LINE);

        $chartYearGenre -> setData([
            'labels' => $dataGraphYearGenre['label'],
            'datasets' => $dataGraphYearGenre['datasets']
        ]);

        return array($queryGraphYearGenre, $chartYearGenre);
    }

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
        $period = $request->request->get('period');
        
        if (is_null($period)){
            $period = 1;
        }
        

        $arrayGraphYearGenre = $this->construcGraphYearGenre($repository, $chartBuilder, $period);

        $queryGraphYearGenre = $arrayGraphYearGenre[0];
        $chartYearGenre = $arrayGraphYearGenre[1];


        $dataGraphYearGenre = $repository->constructArray_DataGraphYearGenre($period);

        $response = new Response(json_encode($dataGraphYearGenre));
        return $response;
    }

}