##### Creation graphe ACP - Projet STEAM #####

## Import des packages
# connection a la BD
library(DBI)
# Manipulation de string => utile pour le setwd()
library(stringr)
# graphes
library(ggplot2)
library(plotly)
library(htmlwidgets)
# Datamining
library(fdm2id)
# Data manipulation => inner_join
library(dplyr)



### nécéssaire d'intaller pandoc sur la machine pour pouvoir y accéder depuis l'invite de cmd ###
### logiciel déjà installer avec Rstudio MAIS pas au bon emplacement !


##### Pour initialiser la sortie des graphes au bon endroit : 
# Etant donner que le code est executer depuis la ligne de cmd, on recupere les arguments donne a la cmd Rscript.exe
# L'argument recupere est le filePath du fichier courant
# On le retravail pour obtenir le path du directory avant de setwd(la path en question)
# Il faut modifier le fichier de sortie pour eviter d'avoir les graphes dans le dossier de la cmd d'execution.

# Obtenir les arguments de la commande
args <- commandArgs(trailingOnly = FALSE)

# Extraire le chemin du fichier
script_path <- sub("--file=", "", args[grep("--file=", args)])

# Convertir le chemin en absolu (si nécessaire)
absolute_path <- normalizePath(script_path, mustWork = TRUE)

if (Sys.getenv("R_BROWSER")=="/usr/bin/open"){
  absolute_path_dir = str_replace(absolute_path, "/[a-zA-Z_]+.R$", "")
  absolute_path_dir = paste(absolute_path_dir, "/results",sep = "")
}else{
  absolute_path_dir = str_replace(absolute_path, "\\\\[a-zA-Z_]+.R$", "")
  absolute_path_dir = paste(absolute_path_dir, "\\results",sep = "")
}

# Afficher le chemin absolu
#print(absolute_path_dir)

tryCatch(
  setwd(absolute_path_dir), 
  error = function(e) {  
    print("error setting working directory")
  }
)

#getwd()





## Connection à la base de donn?es 

# Connect to the MySQL database: con
connexion <- DBI::dbConnect(RMySQL::MySQL(),
                            # A modifier - verifier nom DB.
                            dbname = "e1735u_Projet_Steam_Doctrine", 
                            host = "devbdd.iutmetz.univ-lorraine.fr", 
                            port = 3306,
                            user = "e1735u_appli",
                            password = "32313706")


# # Get table names
tables <- DBI::dbListTables(connexion)

# Display structure of tables
#str(tables)

# Import the accounts table from mydb
games <- DBI::dbReadTable(connexion, "games")
genres = DBI::dbReadTable(connexion, "genres")
games.genres = DBI::dbReadTable(connexion, "link_games_genres")

data_games = as.data.frame(games)
data_genres = as.data.frame(genres)
data_games_genres = as.data.frame(games.genres)

data_macro_genre = inner_join(data_games_genres, data_games, by="app_id")
data_macro_genre = inner_join(data_macro_genre, data_genres, by="genres_id")


dataafter2004 = which(data_games$release_year > 2004)

graphe_diagramme_Jeu_Annee =ggplot(data_games[dataafter2004,], aes(x = release_year, fill = publisher_class)) +
  geom_bar() +
  xlab("Release Year") +
  ylab("Number of New Games") +
  theme_classic() +
  theme(legend.position = "bottom") +
  ggtitle("Evolution of the Number of New Games Per Year Since 2004") +
  scale_fill_manual(values = c("#1f78b4", "#c6dbef", "#9ecae1", "#6baed6"))


ggsave("graphe_diagramme_Jeu_Annee.png", plot=graphe_diagramme_Jeu_Annee)



data_macro_genre['priceDiscretize'] <- discretize(data_macro_genre$price, "cluster", labels = c("low price", "medium price", "high price"))
gamesAA = which(games$publisher_class == "AA")

gamesAA = which(data_macro_genre$publisher_class == "AA      ")

rwperAVGAA = ggplot(data_macro_genre[gamesAA,], aes(x = review_score, y = avg_play_time.x)) +
  geom_point(aes(color = priceDiscretize)) +
  xlab("Review Score") +
  ylab("Average Playtime (Hours)") +
  theme_classic() +
  ggtitle("Overall 'AA' Game Ratings by Average Playtime") +
  scale_color_manual(
    values = c("lightblue", "#3774A5", "#1A3654"),
    name = "Price Categories",
    labels = c("Low", "Medium", "High") 
  )


gamesAAA = which(data_macro_genre$publisher_class == "AAA     ")

rwperAVGAAA = ggplot(data_macro_genre[gamesAAA,], aes(x = review_score, y=avg_play_time.x))+
  geom_point(aes(color = priceDiscretize))+xlab("review score")+ylab("average play time per hours")+theme_classic()+ggtitle("Overall 'AAA' Game Ratings by Average Playtime")+scale_color_manual(
    values = c("lightblue", "#3774A5", "#1A3654"),
    name = "Price Categories",
    labels = c("Low", "Medium", "High") 
  )


gamesIndie = which(data_macro_genre$publisher_class == "Indie   ")

rwperAVGIndie = ggplot(data_macro_genre[gamesIndie,], aes(x = review_score, y=avg_play_time.x))+
  geom_point(aes(color = priceDiscretize))+xlab("review score")+ylab("average play time per hours")+theme_classic()+ggtitle("Overall 'Indie' Game Ratings by Average Playtime")+scale_color_manual(
    values = c("lightblue", "#3774A5", "#1A3654"),
    name = "Price Categories",
    labels = c("Low", "Medium", "High") 
  )

gamesHobbyist = which(data_macro_genre$publisher_class == "Hobbyist")

rwperAVGHobbyist = ggplot(data_macro_genre[gamesHobbyist,], aes(x = review_score, y=avg_play_time.x))+
  geom_point(aes(color = priceDiscretize))+xlab("review score")+ylab("average play time per hours")+theme_classic()+ggtitle("Overall 'Hobbyist' Game Ratings by Average Playtime")+scale_color_manual(
    values = c("lightblue", "#3774A5", "#1A3654"),
    name = "Price Categories",
    labels = c("Low", "Medium", "High") 
  )


# enregistrement des graphes
ggsave("GameAARevPerHours.png", plot=rwperAVGAA)
ggsave("GameAAARevPerHours.png", plot=rwperAVGAAA)
ggsave("GameIndieRevPerHours.png", plot=rwperAVGIndie)
ggsave("GameHobbyistRevPerHours.png", plot=rwperAVGHobbyist)


data_test_graph05 = subset(x = data_macro_genre, subset=data_macro_genre$recommandations < max(data_macro_genre$recommandations))

options(scipen = 999)
quatres.dimensions.copie.revenue = ggplot(data_test_graph05, aes(x = copies_sold, y= revenue)) +
  geom_point(aes(color = review_score, shape = publisher_class)) +
  xlab("Number of copies sold") +
  ylab("Amount of income") +
  ggtitle("Is selling games a source of significant income ?") +
  theme_classic() +
  theme(legend.position = "right") +
  labs(color = "Review Score levels", shape = "Publisher Class")+
  scale_y_continuous(labels =  scales::comma)+
  scale_x_continuous(labels =  scales::comma)

ggsave("quatre_dimensions_copy_sold_revenue.png", plot=quatres.dimensions.copie.revenue)

