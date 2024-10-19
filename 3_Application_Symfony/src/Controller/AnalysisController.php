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
        $data_formated = $repository->constructArray_DataGraphFiveDim();
        
        // Pour comprendre ce qu'il y  a dans cet objet $data_formated
        //dd(json_encode($data_formated));
        //dd($data_formated);
        //dd($dataGraphFiveDim[0]['sommeCopiesSold']);
        //dd($dataGraphFiveDim[0]['label']);
        //dd($dataGraphFiveDim[0]['nbGames']);


        $chart = $chartBuilder->createChart(Chart::TYPE_BUBBLE);



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


        $chart->setData([
            'datasets'=>$data_formated,
        ]);
        
        // $chart->setOptions([
        //     'backgroundColor' => 'rgb(255, 99, 132)',
        //     'borderColor' => 'rgb(255, 99, 132)'
        // ]);

        return $this->render('analysis/index.html.twig', [
            'controller_name' => 'AnalysisController',
            'dataGraphFiveDim' => $dataGraphFiveDim,
            'chart' => $chart
        ]);
    }
}