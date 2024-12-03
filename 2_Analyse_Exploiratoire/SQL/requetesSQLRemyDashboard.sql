# se remettre d'accord sur l'échantillon de jeu selectionnées pour la campagne marketing
# jeux gratuit ? Min de copie vendues ? Date de sortie en 2022 et 2023 ?

# Prix Moyen de vente
SELECT
    ROUND(AVG(price),2) AS PrixMoyen
FROM games ;

# temps de jeu moyen 
SELECT
    ROUND(AVG(avg_play_time),2) AS TempsMoyenHours
FROM games ;

#median pour le nombre de copies vendus

SELECT
  MEDIAN(copies_sold) OVER() AS "Median"
FROM games;

#median pour la variable revenue

SELECT
  MEDIAN(revenue) OVER() AS "Median"
FROM games;
