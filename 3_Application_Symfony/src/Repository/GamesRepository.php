<?php

namespace App\Repository;

use App\Entity\Games;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Games>
 */
class GamesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Games::class);
    }


    public function findDataGraphFiveDim()
    {
        $query = "SELECT 
                        label, 
                        COUNT(*) AS nbGames,
                        SUM(revenue) AS sommeRevenue, 
                        SUM(copies_sold) AS sommeCopiesSold, 
                        AVG(review_score) AS avgReviewScore
                    FROM games
                        JOIN link_games_genres USING (app_id)
	                    JOIN genres USING(genres_id)
                    GROUP BY label";
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }

    public function constructArray_DataGraphFiveDim ()
    {
        $data = $this->findDataGraphFiveDim();
        $background_red = 50;
        $background_green = 60;
        $background_blue = 100;
        $border_red = 80;
        $border_green = 10;
        $border_blue = 120;
        
        
        $result = array();

        foreach ($data as $key) {
            $color_ratio = intval($key["avgReviewScore"]);

            array_push($result, 
                [
                    'label'=>$key["label"],
                    'data'=>[
                        [
                        'x'=>(int) $key["sommeRevenue"],
                        'y'=>(int) $key["sommeCopiesSold"],
                        'r'=>intval($key["nbGames"]/1500),
                        ],
                    ],
                    'backgroundColor'=> "rgb(".$background_red+$color_ratio.",".$background_green+$color_ratio.",".$background_blue+$color_ratio.")",
                    'borderColor' => "rgb(".$border_red+$color_ratio.",".$border_green+$color_ratio.",".$border_blue+$color_ratio.")",
                ]
            );
        };
    
        return $result;
    }

    // Stade expérimental :
    public function findData_Period($period)
    {
        if ($period === 'year'){
            $query = "SELECT DISTINCT release_year
                        FROM games
                        ORDER BY release_year";
            
            }elseif($period === 'month'){
                $query = "SELECT DISTINCT release_month
                        FROM games
                        ORDER BY release_month";
                
                }
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }

    public function findData_Genre()
    {
        $query = "SELECT DISTINCT label
                    FROM genres
                    ORDER BY label";
            
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }

    public function findDataGraphYearGenre()
    {
        $query = "SELECT Label, release_year, SUM(copies_sold) AS sommeCopiesSold
                    FROM games 
                        JOIN link_games_genres USING (app_id)
                        JOIN genres USING(genres_id)
                    GROUP BY genres_id, release_year
                    ORDER BY release_year, Label";
            
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }


    public function constructArray_DataGraphYearGenre ()
    {
        $data_period = $this->findData_Period('year');
        $data_genres = $this->findData_Genre();
        $data_copiesSold = $this->findDataGraphYearGenre();
        //dd($data_period);
        //dd($data_genres);
     
        $labels = array();
        $datasets = array();
        

        // creation des données de l'axes x du LINE CHART
        foreach ($data_period as $year_month) {
            array_push($labels, $year_month['release_year']);
        };

        foreach ($data_genres as $label) {
            $datasets_data = array();
            foreach ($data_copiesSold as $line) {
                    if ($line['Label']===$label["label"]){
                        array_push($datasets_data, $line["sommeCopiesSold"]);
                    };
                }
            array_push($datasets, [
                'label'=> $label["label"],
                'data'=> $datasets_data,
            
            ]);
        };

        $result = ["label" => $labels, "datasets" =>$datasets];
        //dd($result);
        return $result;
    }


    //    /**
    //     * @return Games[] Returns an array of Games objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Games
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}