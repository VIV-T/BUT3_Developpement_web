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
        $dataGraphFiveDim = $repository->findDataGraphFiveDim();
        $dataLabel = $repository->DataGraphFiveDim_label();
        
        // Pour comprendre ce qu'il y  a dans cet objet $dataLabel
        //dd(json_encode($dataLabel));


        $chart = $chartBuilder->createChart(Chart::TYPE_BUBBLE);

        //$chart->setData([
            //'labels' => "['January', 'February', 'March', 'April', 'May', 'June', 'July']",
            //'labels' => $dataLabel,
            //'datasets' => [
                //[
                //   'label' => 'Cookies eaten 🍪',
                //    'backgroundColor' => 'rgb(255, 99, 132, .4)',
                //    'borderColor' => 'rgb(255, 99, 132)',
                //    'data' => [2, 10, 5, 18, 20, 30, 45],
                //    'tension' => 0.4,
                //],
                //[
                //    'label' => 'Km walked 🏃‍♀️',
                //    'backgroundColor' => 'rgba(45, 220, 126, .4)',
                //    'borderColor' => 'rgba(45, 220, 126)',
                //    'data' => [10, 15, 4, 3, 25, 41, 25],
                //    'tension' => 0.4,
                //],
            //],
        //]);

        // $chart->setData([
        // 'label' => ['test1', 'test2'],
        // 'datasets'=>[
        //     [
        //         'x' => 0.2,
        //         'y' => 0.3,
        //         'r' => 15
        //     ],
        //     [
        //         'x' => 0.4,
        //         'y' => 0.1,
        //         'r' => 10
        //     ]
        //     ],
        //     ]);

        $chart->setData([
        'datasets'=>[
            [
                'label' => 'Dataset 1',
                'data' => [
                    [
                        'x' => 20,
                        'y' => 30,
                        'r' => 15
                    ],
                ],
                'backgroundColor' => 'rgb(255, 99, 132)'
            ],
            [
                'label' => 'Dataset 2',
                'data' => [
                    [
                        'x' => 40,
                        'y' => 10,
                        'r' => 10
                    ],
                ],
                'backgroundColor' => 'rgb(99, 255, 132)'
            ],
            ],
            ]);

        //$chart->setOptions([
        //    'maintainAspectRatio' => false,
        //]);
        $chart->setOptions([
            'backgroundColor' => 'rgb(255, 99, 132)',
            'borderColor' => 'rgb(255, 99, 132)'
        ]);

        return $this->render('analysis/index.html.twig', [
            'controller_name' => 'AnalysisController',
            'dataGraphFiveDim' => $dataGraphFiveDim,
            'chart' => $chart
        ]);
    }
}