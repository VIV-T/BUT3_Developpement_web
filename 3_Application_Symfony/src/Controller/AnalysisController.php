<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// To get data with SQL query in the repository
use App\Repository\GamesRepository;

// To make chart with symfony UX - chartjs
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class AnalysisController extends AbstractController
{
    #[Route('/analysis', name: 'app_analysis')]
    public function index(GamesRepository $repository, ChartBuilderInterface $chartBuilder): Response
    {
        // Appel de la fonction de création du 1er graphique - GraphFiveDim
        $arrayGraphFiveDim = $this->construcGraphFiveDim($repository, $chartBuilder);
        //dd($arrayGraphFiveDim);
        
        $queryGraphFiveDim = $arrayGraphFiveDim[0];
        $chartFiveDim = $arrayGraphFiveDim[1];
        
        // Appel de la fonction de création du 2nd graphique - GraphYearGenre
        $arrayGraphYearGenre = $this->construcGraphYearGenre($repository, $chartBuilder);
        //dd($arrayGraphYearGenre);
        
        $queryGraphYearGenre = $arrayGraphYearGenre[0];
        $chartYearGenre = $arrayGraphYearGenre[1];


        return $this->render('analysis/index.html.twig', [
            'controller_name' => 'AnalysisController',
            'viewGraphFiveDim' => $queryGraphFiveDim,
            'chartFiveDim' => $chartFiveDim,
            'viewGraphYearGenre' => $queryGraphYearGenre,
            'chartYearGenres' => $chartYearGenre,
        ]);
    }

    
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

        // Code qui fonctionne pour le Bubble chart test
        // $chart->setData([
        // 'datasets'=>[
        //     [
        //         'label' => 'Dataset 1',
        //         'data' => [
        //             [
        //                 'x' => 20,
        //                 'y' => 30,
        //                 'r' => 15
        //             ],
        //         ],
        //         'backgroundColor' => 'rgb(255, 99, 132)'
        //     ],
        //     [
        //         'label' => 'Dataset 2',
        //         'data' => [
        //             [
        //                 'x' => 40,
        //                 'y' => 10,
        //                 'r' => 10
        //             ],
        //         ],
        //         'backgroundColor' => 'rgb(99, 255, 132)'
        //     ],
        //     ],
        //     ]);


        // $chart->setData([
        //     'datasets'=>[
        //      [
        //         'label' => $dataGraphFiveDim[0]['label'],
        //          'data' => [
        //              [
        //                 'x' => (int) $dataGraphFiveDim[0]['sommeRevenue'],
        //                 'y' => (int) $dataGraphFiveDim[0]['sommeCopiesSold'],
        //                 //'r' => $dataGraphFiveDim[0]['nbGames'],
        //                 'r' => intval($dataGraphFiveDim[0]['nbGames']/3000),
        //                 ],
        //          ],
        //          'backgroundColor' => 'rgb(255, 99, 132)',
        //      ],
        //     ],
        // ]);


        $chartFiveDim->setData([
            'datasets'=>$dataGraphFiveDim,
        ]);
        //dd($queryGraphFiveDim);

        return array($queryGraphFiveDim, $chartFiveDim);
    }
    
    // Stade expérimental :
    public function construcGraphYearGenre(GamesRepository $repository, ChartBuilderInterface $chartBuilder): Array
    {
        $queryGraphYearGenre= $repository->findData_Period("year");
        $dataGraphYearGenre = $repository->constructArray_DataGraphYearGenre();
        //dd($dataGraphYearGenre);
        //$queryGraphYearGenre = 0;
        //dd($queryGraphYearGenre);

        $chartYearGenre = $chartBuilder->createChart(Chart::TYPE_LINE);

        // $chartYearGenre -> setData([
        //     'labels' => $dataGraphYearGenre['label'],
        //     'datasets' => [
        //         [
        //             'label' => 'My First dataset',
        //             'backgroundColor' => 'rgb(255, 99, 132)',
        //             'borderColor' => 'rgb(255, 99, 132)',
        //             'data' => [0, 10, 5, 2, 20, 30, 45],
        //         ],
        //     ],
        // ]);

        $chartYearGenre -> setData([
            'labels' => $dataGraphYearGenre['label'],
            'datasets' => $dataGraphYearGenre['datasets']
        ]);

        // $chartYearGenre -> setData($dataGraphYearGenre);

        //dd($dataGraphYearGenre);

        return array($queryGraphYearGenre, $chartYearGenre);
    }
}