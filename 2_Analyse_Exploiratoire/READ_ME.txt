L'objectif de cette partie Analyse Exploratoire est de faire de la fouille exploratoire dans les données afin d'identifier les variables d'intérêts 
pour la construction de graphiques mais aussi de s'intérresser à la façon dont nous allons valoriser notre jeu de données.

Pour cette partie, nous avons principalement utilisé R avec l'IDE RStudio.
Nous avons importer les fichier csv avant faire des analyses simple dessus. Nous avons très vite identifier la nécessité de filtrer 
notre jeu de données afin de ne prendre en compte que les données pertinentes pour une analyse macro-économique du marché du jeu vidéo.

En effet, le nombre de jeux n'ayant aucune réelle présence sur le marché est relativement important, nous avons donc décidé de filtrer arbitrairement 
notre jeu de données en fonction de critère statistiques (moyenne, médiane, quartile, ...).

Après cela, il nous a semblé important de replacer nos données dans leur contexte. Le but de cette application est de fournir des conseil et recommandations au
décisionnaire marketing de l'entreprise en vue d'une future campagne promotionnelle. Dans cette optique, intégrer des jeux gratuits ou avec un prix très faible 
à notre système de recommandation ne semble pas pertinent.

Après cette segmentation des données, nous avons expérimenté quelques graphe afin d'avoir un meilleur aperçu de nos possibilité de valorisation de nos données.
De plus nous avons aussi pu créer des "View" en SQL adaptées au graphe que nous avions imaginés avant des les importer dans R Studio pour tester leur bonne réalisation.

Nous avons aussi mis en place des requêtes SQL pour pouvoir mettre en place des aggrégats, des statistiques et des potentiels KPI ensuite utilisables dans l'application.
Dernière modification : 15/10/2024