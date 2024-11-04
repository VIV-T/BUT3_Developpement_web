<?php

namespace App\Repository;

use App\Entity\Dashboard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dashboard>
 */
class DashboardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dashboard::class);
    }


    // Appel AJAX - Select
    public function findDataSelectedGames()
    {
        // on récupère toutes les données des jeux selectionnés pour le dashboard
        $query = "SELECT *
                    FROM dashboard";
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }


    // Appel AJAX - Select & Unselect
    public function findDataTableGames($appID)
    {
        // utile pour les tests
        if(is_null($appID)){
            $appID = 330720;
        }

        // on récupère toutes les données associées au jeu dans la table game
        $query = "SELECT *
                    FROM games
                    WHERE app_id = $appID";
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        //dd($result);
        return $result->fetchAll();
    }


    // Appel AJAX - Select
    public function insertIntoDashboardTable(Array $dataGameToInsert) : Bool
    {
        //insérer les données récupérées de la table games dans la table Dashboard.
        $query = "INSERT INTO dashboard (
                            app_id, 
                            game_name,
                            release_date, 
                            release_month,
                            release_year, 
                            pegi,
                            english_supported, 
                            header_img,
                            notes,
                            categories, 
                            publisher_class, 
                            publishers, 
                            developers, 
                            systems, 
                            copies_sold, 
                            revenue, 
                            price, 
                            avg_play_time, 
                            review_score, 
                            achievements, 
                            recommandations 
                                ) 
                            VALUES (
                            :app_id, 
                            :game_name,
                            :release_date, 
                            :release_month,
                            :release_year, 
                            :pegi,
                            :english_supported, 
                            :header_img,
                            :notes,
                            :categories, 
                            :publisher_class, 
                            :publishers, 
                            :developers, 
                            :systems, 
                            :copies_sold, 
                            :revenue, 
                            :price, 
                            :avg_play_time, 
                            :review_score, 
                            :achievements, 
                            :recommandations  
                                )";

        
        $result = $this->getEntityManager()->getConnection()->prepare($query)->execute($dataGameToInsert);
        $result->fetchAll();

        return TRUE;
    }



    public function truncateDashboardTable()
    {
        // on récupère toutes les données des jeux selectionnés pour le dashboard
        $query = "TRUNCATE TABLE dashboard";
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        $result->fetchAll();
        
        return TRUE;
    }


    // Appel AJAX - Unselect
    public function deleteGameFromDashboardTable($appID)
    {
        // on récupère toutes les données des jeux selectionnés pour le dashboard
        $query = "DELETE FROM dashboard
                    WHERE app_id = $appID;";
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        $result->fetchAll();

        return TRUE;
    }


    // Appel AJAX - Count
    public function countSelectedGames()
    {
        // on récupère toutes les données des jeux selectionnés pour le dashboard
        $query = "SELECT COUNT(*) AS account
                    FROM dashboard";
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }



    
}