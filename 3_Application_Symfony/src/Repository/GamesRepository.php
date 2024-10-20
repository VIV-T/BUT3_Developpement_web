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
            //dd($color_ratio);

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