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


    /////
    ///// Page Analysis
    /////

    /// Premier graphique
    ///

    // Requête SQL
    //
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

    // Mise en forme des données pour chart js
    //
    public function constructArray_DataGraphFiveDim ()
    {
        // Appel de la requête SQL - obtention des données
        $data = $this->findDataGraphFiveDim();
        

        
        /// création des variable pour la partie visuelle (couleurs & taille)
        // extarction des informations utiles
        $array_reviewScore = [];
        $array_nbGames = [];
        foreach ($data as $key) {
            $array_reviewScore[]=$key["avgReviewScore"];
            $array_nbGames[]=$key["nbGames"];
        }
        
        $min_ref_color_reviewScore = min($array_reviewScore);
        $ref_color_reviewScore = max($array_reviewScore)-$min_ref_color_reviewScore;
        $min_ref_size_nbGames = min($array_nbGames);
        $max_ref_size_nbGames = max($array_nbGames);
        $ref_size_nbGames = max($array_nbGames)-$min_ref_size_nbGames;

        // variables de références
        $background_red = 0;
        $background_green = 60;
        $background_blue = 135;
        $size_reference = 5;
        
        
        
        // création du tableau qui sera renvoyé
        $result = array();

        // remplissage du tableau avec les données de la reqête
        // a modifier notamment tout ce qui concerne les couleurs -  modifier ici.
        foreach ($data as $key) {
            // ce ratio est un pourcentage de l'ecart maximum entre les reviewScore des genres
            $color_ratio = round((intval($key["avgReviewScore"])-$min_ref_color_reviewScore)/$ref_color_reviewScore,2);
            $size_ratio = 1+round((intval($key["nbGames"])-$min_ref_size_nbGames)/$ref_size_nbGames,2)*4;
            

            array_push($result, 
                [
                    'label'=>$key["label"],
                    'data'=>[
                        [
                        'x'=>(int) $key["sommeRevenue"]/10**9,
                        'y'=>(int) $key["sommeCopiesSold"]/10**6,
                        'r'=>ceil($size_reference*$size_ratio),
                        ],
                    ],
                    // la couleur dépend du reviewScore
                    // on utilise la var $color_ratio pour ajouter un pourcentage de la couleur de base au différentes composante de la couleur.
                    'backgroundColor'=> "rgba(".$background_red+$color_ratio*$background_red.",".$background_green+$color_ratio*$background_green.",".$background_blue+$color_ratio*$background_blue.", 0.6)",
                    
                ]
            );
        };
        
        return $result;
    }

    
    
    /// Second graphique
    ///

    // Requêtes SQL
    //
    
    // Pour obtenir un array de mois/années - condition sur la periode
    public function findData_Period($period)
    {
        if ($period === 'year'){
            $query = "SELECT DISTINCT release_year
                        FROM games
                        ORDER BY release_year";
            
            }elseif($period === 'month'){
                $query = "SELECT DISTINCT release_month
                        FROM games JOIN referenciel_month ON games.release_month = referenciel_month.name
                        ORDER BY referenciel_month.number";
                
                }
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }

    
    // Pour obtenir un array des genres
    public function findData_Genre()
    {
        $query = "SELECT DISTINCT label
                    FROM genres
                    ORDER BY label";
            
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }

    
    // Reqête principale - condition sur la periode
    // Utilisation du code de création des vues sur la BD test
    public function findDataGraphYearGenre($period)
    {
        if ($period === 'year'){
            $query = "SELECT Label, release_year, SUM(copies_sold) AS sommeCopiesSold
                    FROM games 
                        JOIN link_games_genres USING (app_id)
                        JOIN genres USING(genres_id)
                    GROUP BY genres_id, release_year
                    ORDER BY release_year, Label";
            
            }elseif($period === 'month'){
                $query = "SELECT Label, release_month, SUM(copies_sold) AS sommeCopiesSold
                    FROM games 
                        JOIN link_games_genres USING (app_id)
                        JOIN genres USING(genres_id)
                    GROUP BY genres_id, release_month
                    ORDER BY release_month, Label";
                
                }
        
            
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }


    // Mise en forme des données pour chart js
    //
    public function constructArray_DataGraphYearGenre ($period)
    {
        //$period = "year";
        //$period = "month";

        // Appel des requêtes SQL précédentes
        $data_period = $this->findData_Period($period);
        $data_genres = $this->findData_Genre();
        $data_copiesSold = $this->findDataGraphYearGenre($period);
     
        // Création d'objets vides - présents dans le resultats
        $labels = array();
        $datasets = array();
        
        // Nt :  voir documentation chart js - structure des données d'un chart LINETYPE
        // creation des données de l'axes x du LINE CHART
        foreach ($data_period as $year_month) {
            array_push($labels, $year_month['release_'.$period]);
        };

        
        // variables pour les couleurs
        $compteur_couleur = 1;
        $max_compteur_couleur = 9;
        $border_red = 0;
        $border_green = 60;
        $border_blue = 135;

        // creation des données du graphes - a partir des données des requêtes
        foreach ($data_genres as $genre) {
            $datasets_data = array();
            foreach ($data_copiesSold as $line) {
                if ($line['Label']===$genre["label"]){
                    array_push($datasets_data, $line["sommeCopiesSold"]);
                };
            }
            
            //vérification de la longueur du tableau de données pour chaque genre
            // Si le tableau n'est pas complet, on rajoute des 0 au debut 
            //(les données eco sont connues pour tous les genres dans les dernière années mais pas forcement 
            //dans les années 90-2000)
            if (count($datasets_data)<count($labels)){
                $add_zero_limit = count($labels)-count($datasets_data);
                //dd($add_zero_limit);
                for ($i = 0; $i < $add_zero_limit; $i++) {
                    array_splice($datasets_data, 0,0,0);
                }
                //dd($datasets_data);
            };
            
            // intégration des données dans les objets de résultats $label et $data + paramètres visuels
            $color_ratio = $compteur_couleur/$max_compteur_couleur;
            array_push($datasets, [
                'label'=> $genre["label"],
                'data'=> $datasets_data,
                'backgroundColor'=>"rgba(255,225,255,0)",
                'borderColor'=> "rgba(".$border_red+$color_ratio*$border_red.",".$border_green+$color_ratio*$border_green.",".$border_blue+$color_ratio*$border_blue.", 0.8)"
            ]);
            $compteur_couleur = $compteur_couleur +1;
        };

        $result = [
            "data"=>[
                "labels" => $labels, 
                "datasets" =>$datasets
            ], 
            "options"=>[
                "scales"=>[
                    "x"=>[
                        "title"=>[
                            "display" => true, 
                            "text" => $period
                        ]
                    ], 
                    "y"=>[
                        "title"=>[
                            "display" => true, 
                            "text" => 'Copies Sold (Millions)'
                        ]
                    ]
                ]
            ]
        ];
        return $result;
    }



    /////
    ///// Page Recommancations
    /////

    /// Données non-filtrées
    ///
    public function get_all_recommandations_data($last_promotion_year){
        $query = "SELECT DISTINCT app_id, copies_sold, revenue, avg_play_time, recommandations, header_img, review_score
                    FROM games
                    WHERE release_year >=".$last_promotion_year." AND price > 10
                    ORDER BY copies_sold DESC";
        
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }


    /// Modal
    ///

    // Requête SQL
    //
    public function findDataGameModal($appID)
    {
        // utile pour les tests
        if(is_null($appID)){
            $appID = 10;
        }

        // on récupère toutes les données associées au jeu
        $query = "SELECT app_id, game_name, release_date, release_month, release_year, pegi, english_supported, header_img, notes, categories, publisher_class, publishers, developers, systems, copies_sold, revenue, price, games.avg_play_time, review_score, achievements, recommandations, GROUP_CONCAT(label SEPARATOR ', ') AS labels
                    FROM games
	                    JOIN link_games_genres USING (app_id)
	                    JOIN genres USING(genres_id)
                    WHERE app_id = $appID
                    GROUP BY app_id";
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }


    // Requêtes SQL

    
    public function get_genres_list(){
        $query = "SELECT label
                    FROM genres 
                    ORDER BY label";
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }


    public function get_publisherClass_list(){
        $query = "SELECT DISTINCT publisher_class
                    FROM games 
                    ORDER BY publisher_class";
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }



    /////////
    ///////// Requête SQL : Filtrage AJAX
    /////////
    
    
    public function get_subseted_data($parameters){
        $where_query = $this->construct_where_query($parameters);
        
        $query = "SELECT DISTINCT app_id, copies_sold, revenue, avg_play_time, recommandations, header_img
                    FROM games
	                    JOIN link_games_genres USING (app_id)
	                    JOIN (SELECT genres_id, label FROM genres) AS genres USING(genres_id)
                    ".$where_query;
        
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }


    public function construct_where_query($parameters){
        $params_genres = $parameters[0];
        $params_publisherClass = $parameters[1];
        $param_orderBy = $parameters[2]; 
        $array_params_slider = $parameters[3];

        $array_where_query_genre = [];
        $array_where_query_publisherClass = [];

        // Genres
        foreach ($params_genres as $genre){
            $query_genre = 'genres.label LIKE "'.$genre.'"';
            $array_where_query_genre []= $query_genre;
        }
        $where_query_genres = implode(" OR ", $array_where_query_genre);
        
        //PublisherClass
        foreach ($params_publisherClass as $publisherClass){
            $query_publisherClass = 'games.publisher_class LIKE "'.$publisherClass.'"';
            $array_where_query_publisherClass []= $query_publisherClass;
        }
        $where_query_publisherClass = implode(" OR ", $array_where_query_publisherClass);
        
        $where_query_part1= "(".$where_query_genres . ") AND (" . $where_query_publisherClass.")";

        $where_query_part2 = $this->construct_where_query_parms_slider($array_params_slider);

        return "WHERE games.release_year >= 2022 AND games.price > 10 AND ".$where_query_part1 ." AND ". $where_query_part2." ORDER BY games.".$param_orderBy." DESC";

    }


    function construct_where_query_parms_slider($array_params_slider){
        $params_copies_sold = $array_params_slider[0];
        $params_revenue = $array_params_slider[1];
        $params_review_score = $array_params_slider[2];
        $params_recommandations = $array_params_slider[3];

        $query_copies_sold = "games.copies_sold>".$params_copies_sold[0]." AND games.copies_sold<".$params_copies_sold[1];
        $query_revenue = "games.revenue>".$params_revenue[0]." AND games.revenue<".$params_revenue[1];
        $query_review_score = "games.review_score>".$params_review_score[0]." AND games.review_score<".$params_review_score[1];
        $query_recommandations = "games.recommandations>".$params_recommandations[0]." AND games.recommandations<".$params_recommandations[1];

        $where_query_part2 = "(".$query_copies_sold.") AND (".$query_revenue.") AND (".$query_review_score.") AND (".$query_recommandations.")";

        return $where_query_part2;
    }



    //// KPI
    // Requêtes SQL
    public function get_perc_games_revenue_lt_1000(){
        $query = "SELECT ROUND(games_revenue_lt_1000/tot_games, 4) AS perc_games_revenue_lt_1000
                    FROM (SELECT count(*) AS games_revenue_lt_1000
                            FROM games 
                            WHERE revenue < 1000) AS temp1 
                    CROSS JOIN (SELECT count(*) AS tot_games
                                FROM games ) AS temp2";
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }


    public function get_perc_top1000_games_revenue(){
        $query = "SELECT ROUND(sum(revenue) / cumul_revenue_tot, 4) AS part_revenue_top1000
                    FROM (
                        SELECT *
                        FROM e1735u_Projet_Steam_Doctrine.games
                        ORDER BY revenue DESC LIMIT 1000
                    ) AS temp 
                    CROSS JOIN (
                        SELECT sum(revenue) AS cumul_revenue_tot
                        FROM e1735u_Projet_Steam_Doctrine.games
                    ) AS temp_tot";
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }


    public function get_sum_tot_revenue_2024(){
        $query = "SELECT SUM(revenue) AS somme_tot_revenue
                    FROM games
                    WHERE release_year=2024";
        
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        return $result->fetchAll();
    }

}