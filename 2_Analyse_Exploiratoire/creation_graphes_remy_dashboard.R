##### Creation graphe ACP - Projet STEAM #####

## Import des packages - détailler pourquoi on les utilises
library(DBI)
library(ggplot2)
library(fdm2id)
library(plotly)
library(htmlwidgets)
library(stringr)
library(dplyr)
library(ggrepel)

##### Pour initialiser la sortie des graphes au bon endroit : 
# etant donner que le code est executer depuis la ligne de cmd, on recupere les arguments donne a la cmd Rscript.exe
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
print(absolute_path_dir)

tryCatch(
  setwd(absolute_path_dir), 
  error = function(e) {  
    setwd("C:/Users/TV/Documents/Thib/Metz/Etudes/BUT_3/dvp_web/ProjetSteam/2_Analyse_Exploiratoire")
  }
)

getwd()





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


jeu_par_publisher_class <- table(data_games$publisher_class)
tableau_pc <- as.data.frame(jeu_par_publisher_class)


df2 <- tableau_pc %>% 
  mutate(csum = rev(cumsum(rev(Freq))), 
         pos = Freq/2 + lead(csum, 1),
         pos = if_else(is.na(pos), Freq/2, pos))

piechart_publishersclass = ggplot(tableau_pc, aes(x="", y=Freq, fill=Var1)) +
  geom_bar(stat="identity", width=1) +
  coord_polar("y", start=0) +
  xlab("") +
  ylab("") +
  ggtitle("Distribution of publisher class games") +
  scale_fill_manual(values = c("#1f78b4", "#c6dbef", "#9ecae1", "#6baed6"), name = "Publisher class") +
  theme_classic() +
  theme(
    legend.position = "bottom",
    panel.background = element_rect(fill = "white", color = NA),
    plot.background = element_rect(fill = "white", color = NA),
    axis.ticks = element_blank(),             # Supprime les ticks des axes
    axis.text = element_blank()               # Supprime les étiquettes des axes
  ) +
  geom_label_repel(
    data = df2,
    aes(y = pos, label = Freq),
    size = 4.5,
    nudge_x = 1,
    show.legend = FALSE
  )

ggsave("pie_chart_pc_dashboard.png", plot=piechart_publishersclass)


scatterplot_dashboard = ggplot(data_games, aes(x = review_score, y = price)) +
  geom_point(color = "#3774A5") +
  xlab("Review Score") +
  ylab("Price") +
  theme_classic() +
  ggtitle("Global position of videos games considering their prices and ratings")

ggsave("scatterplot_dashboard.png", plot=scatterplot_dashboard)
