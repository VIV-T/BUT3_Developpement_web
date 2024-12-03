
# KPI Prix Moyen de vente

SELECT
    ROUND(AVG(price),2) AS PrixMoyen
FROM games ; #adapter à la base de données des jeux selectionnes

# temps de jeu moyen par personne en H
SELECT
    ROUND(AVG(avg_play_time),2) AS TempsMoyenHours
FROM games ;

#median pour le nombre de copies vendus
# la valeur médiane du nombre copies vendues est de..
# Phrase d'interpretation : Au moins 50% des jeux ont un nombre de copie vendues supérieur à..

SELECT
  MEDIAN(copies_sold) OVER() AS "Median"
FROM games;

#median pour la variable revenue
# la valeur médiane du revenue est de..
# Phrase d'interpretation : Au moins 50% des jeux ont un montant de revenu supérieur à..

SELECT
  MEDIAN(revenue) OVER() AS "Median"
FROM games;

# Pourquoi ajouter un nombre qui précise le nombre de jeux selectionnés mais un peu redondant (présent sur page
#recommandations 
select count(*) 
FROM e1735u_Projet_Steam_Doctrine.games;

#  integrer dans l'appli algo OK
# requete pour obtenir le top 3 des jeux en tete d'affiche pour la campagne marketing parmis les jeux selectionnés
# rajouter tous les champs de la tables games (ou bien que l'image et les indicateurs quantitatifs ?)
select 
	app_id,
	(1/5)*(copies_sold/max_copies_sold + revenue/max_revenue + avg_play_time/max_avg_play_time + review_score/max_review_score + recommandations/max_recommandations) as indice_for_top
from games JOIN (select 
	max(copies_sold) as max_copies_sold,
    max(revenue) as max_revenue,
    max(avg_play_time) as max_avg_play_time,
    max(review_score) as max_review_score,
    max(recommandations) as max_recommandations
from games
) as tot_games_quanti
Order by indice_for_top desc limit 3;

#SELECT * FROM e1735u_Projet_Steam_Doctrine.games
#;


#select sum(revenue)/count(*) as revenus_per_game
#FROM e1735u_Projet_Steam_Doctrine.games;


#select sum(revenue), sum(copies_sold)
#FROM e1735u_Projet_Steam_Doctrine.games;

# revenu cumulée des 100 jeux les plus vendeurs ??
# chiffre brut trop peu exploitable + un peu de mal à voir interet

#select 
#	sum(revenue) as cumul_revenue_top100,
#	sum(copies_sold) as cumul_copiesSold_top100
#from (
#select *
#FROM e1735u_Projet_Steam_Doctrine.games
#order by revenue DESC LIMIT 100
#) as temp ;

# idem à celle du dessus sauf 1000 à la place de 100 ????

# Part des revenues des 1000 Jeux avec le plus de revenue dans le total
select 
	ROUND(sum(revenue) / cumul_revenue_tot, 2) AS part_revenue_top1000
from (
select *
FROM e1735u_Projet_Steam_Doctrine.games
order by revenue DESC LIMIT 1000
) as temp CROSS JOIN (select 
	sum(revenue) as cumul_revenue_tot
from e1735u_Projet_Steam_Doctrine.games) as temp_tot ;



# perc_games_revenue_lt_1000
SELECT (games_revenue_lt_1000/tot_games) AS perc_games_revenue_lt_1000
FROM (select count(*) as games_revenue_lt_1000
from games 
where revenue < 1000) as temp1 CROSS JOIN (select count(*) as tot_games
from games ) AS temp2
;

# sum revenue of 2024 games
SELECT SUM(revenue) AS somme_tot_revenue
FROM games
WHERE release_year=2024
;

