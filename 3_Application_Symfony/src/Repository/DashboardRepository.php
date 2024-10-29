<?php

namespace App\Repository;

use App\Entity\Games;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Games>
 */
class DashboardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Games::class);
    }

    public function findDataSelectedGames()
    {
        // on récupère toutes les données des jeux selectionnés pour le dashboard
        $query = "SELECT *
                    FROM dashboard";
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }

    public function findDataTableGames($appID)
    {
        // utile pour les tests
        if(is_null($appID)){
            $appID = 10;
        }

        // on récupère toutes les données associées au jeu dans la table game
        $query = "SELECT *
                    FROM games
                    WHERE app_id = $appID";
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }

    public function insertIntoDashboardTable(Array $dataGameInsert) : Bool
    {
        //insérer les données récupérées de la table games dans la table Dashboard.


        return TRUE;
    }

}