Le but de la préparation de données est de construire une base de données utilisable pour le développement et la construction des graphiques.

Remarque préliminaire :
Dans les dossiers donneeBrutes et donneesTransformees, dézipper tous les fichier en les plaçant dans le répertoire courant. 

I/ Explications
Première étape : Collecte des données
Il nous a fallu collecter les données à partir du site : https://gamalytic.com/game-list
Ce site de données en OpenData nous propose de télécharger les fichiers de données, mais uniquement 50 lignes à la fois (sur plus de 80_000).

Afin de gagner du temps, nous avons développé un code de web scrapping sur Python et plus précisément avec le package BeautifulSoup qui permet cela.
Nous avons ainsi téléchargé massivement les données du site (en OpenData, donc rien d'illégal !). 
Il a ensuite fallu télécharger les données du Cloud en local afin de les exploiter.
Les données brutes ne sont pas vraiment utilisables telles quelles au vu de nos objectifs de développement.

Deuxième étape : Nettoyage des données et création d'une Base de Données exploitable

Remarques : Nous avons créé une base de données pour tester et vérifier que notre code fonctionnait avant d'insérer les données dans
			la table créée avec Doctrine dans Symfony. Nous nous concentreront ici sur nos fichiers tests. Avec notre base de données de tests, 
			les tables ont effectivement été créées avec le fichier : creation_tables_projet_dvp_web.sql
			Cependant, pour notre base de données réelle, les tables ont été créées avec l'outil Doctrine intégré au framework Symfony. 
			La création des tables s'est faite à l'aide d'entités et de migrations (expliqué plus tard dans le projet).

Avant l'insertion des données de test, il nous a fallu créer nos tables SQL. Cela a été réalisé avec le fichier : creation_tables_projet_dvp_web.sql

L'utilisation de Pentaho Data Integration (PDI) est essentielle dans cette partie du travail. 
En effet, l'outil d'ETL va nous permettre de nettoyer les données et de les insérer dans un Système de Gestion de Bases de Données (SGBD).
Le travail réalisé avec PDI a pu être dupliqué dans 2 fichiers différents. 

	1. Extraction et Nettoyage des données. 
	Les données sont stockées dans divers fichiers CSV. PDI va nous permettre de les réunir et de les joindre quand nécessaire.
	De plus, cet outil offre de nombreuses fonctionnalités permettant le nettoyage des données notamment 
	en matière de manipulation de chaines de caractères et en termes de changement de type et de format.

	2. Export en CSV et insertion dans les tables SQL.
	Après avoir réuni les nombreux fichiers, il nous a fallu exporter nos fichiers finaux (qui sont l'équivalent en CSV de nos tables SQL).
	Cet export nous a ensuite permis la réutilisation de ces fichiers pour insérer les données dans les tables toujours avec PDI.
	Il nous a aussi permis de faire une analyse exploratoire sur les données avec le langage R.


La réalisation du 1. et l'export des fichiers à l'issue de celui-ci se fait dans le fichier : Extraction_Nettoyage_ExportCSV.ktr
Toute la partie d'insertion dans les tables se fait dans les fichiers suivants :
	- Insertion_Workbench_games.ktr
	- Insertion_Workbench_genres.ktr
	- Insertion_Workbench_link_games_genres.ktr
	
Il est possible d'utiliser une "Tâche" (.kjb) de PDI, qui est un fichier de planification d'exécution d'autres programmes de PDI, les "Transformations" (.ktr).
La "Tâche" utilisée ici nous sert à ordonner l'exécution des différents fichiers (nettoyage + insertion dans les tables) afin d'ordonner et de centraliser notre travail.
Ce fichier est : Tache_Insertion_tables_Workbench.kjb



Remarques : Afin de simplifier l'utilisation du programme par les divers membres du groupe, nous avons créé des variables d'environemment modifiables.
Le but de ces variables est de gérer la connexion à la base de données locale de chacun afin de pouvoir travailler sur les mêmes 
données de manière indépendante et de pouvoir progresser dans l'avancement du projet sans être dépendant des autres. 
Ces variables sont stockées dans le fichier : ressources/config.txt



II/ Instructions pour la reproduction

1. Exécuter le code de webscrapping (dans google colab):
2. Dans un dossier donneesBrute, insérer les données téléchargées depuis google colab mais aussi depuis les autres sources de données.

Deux options possibles dans la suite :
Option 1-
	3. Exécuter la requête de création de table SQL dans le schéma associé.
	4. Exécuter la tâche du fichier "Traitement_PDI_Workbench" en paramétrant les transformations et tâches PDI pour insérer les données dans la bonne BD.

Option 2-
	3. Créer les tables dans la base de données à partir d'entité Doctrine, et de migrations. (Voir partie Doctrine)
	4. Exécuter la tâche du fichier "Traitement_PDI_Workbench_Doctrine" en paramétrant les transformations et tâches PDI pour insérer les données dans la bonne BD.


Dernière modification : 15/10/2024