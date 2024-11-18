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

absolute_path_dir = stringr::str_replace(absolute_path, "\\\\[a-zA-Z_]+.R$", "")
absolute_path_dir = paste(absolute_path_dir, "\\results",sep = "")

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

data_games = as.data.frame(games)


## Realisation de l'ACP 

# mettre que les donn?es quantitative dans l'ACP
# variables utilisees :
#  - copiesSold
#  - revenue
#  - recommandations

data_games.pca = fdm2id::PCA(data_games[,c(15:19,21)])

# inertie
inertie_premier_plan_factoriel = data_games.pca$eig[2,3]



## Application du K-MEANS sur les coordonn?es de l'ACP

# détermination du nombre de cluster optimal
# fdm2id::kmeans.getk(data_games.pca$ind$coor)
# Ici, la valeur est fixée à 2 car le nb de cluster optimal peut changer en fonction de l'itération, seulement dans la plupart des cas entre 7 à 8 fois sur 10, ce nombre est deux.
# Pour des raison de perfomance et de cohérence, on fixe cette valeur à 2.
nb_cluster_opti=2

# application du kmeans
data_games_ACP_kmeans = fdm2id::KMEANS(data_games.pca$ind$coord, k=nb_cluster_opti)


# recup?ration individuellement des donn?es des clusters 

# creation d'un dataframe vide pour accueillir les donn?es de la table games 
# avec leur nouvelle var d?signant leur cluster associ?.
data_games_clust = data.frame(Dim1=numeric(), Dim2=numeric(), Dim3=numeric(), Dim4=numeric(), Dim5=numeric(), Dim6=numeric(), cluster=factor())

for (i in 1:nb_cluster_opti) {
  # r?cuperation de tous les index de la table de donn?es du premier cluster
  index_data_clust = as.vector(which(data_games_ACP_kmeans$cluster == i))
  
  # extraction des donn?es ? partir de l'index et de la table originale.
  data_games_clust_temp = as.data.frame(data_games.pca$ind$coord[index_data_clust,])
  
  # ajout de la colonne associ?e au cluster
  data_games_clust_temp[, "cluster"] = i
  
  # fusion avec le dataset de resultat.
  data_games_clust = rbind(data_games_clust, data_games_clust_temp)
}


data_games.pca$ind$coord=as.matrix(data_games_clust)
data_games_clust =data_games_clust[,c(1,2,7)]


# enregistrement des graphes de l'ACP : 
# - cercles des correlations
# - projection sur le premier plan factoriel
# - renvoyer aussi l'inertie totale du premier plan factoriel


#graphe_ACP_cercle_cor = plot(data_games.pca, type = "cor")
#ggsave("graphe_ACP_cercle_cor.png", plot=graphe_ACP_cercle_cor)

#graphe_ACP_projection_points = plot(data_games.pca)
#ggsave("graphe_ACP_projection_points.png", plot=graphe_ACP_projection_points)


# si possible, rajouter inertie du premier plan factoriel (var inertie_premier_plan_factoriel) et l'ajouter au graphe.

fig <- plot_ly(data_games_clust, x = ~Dim.1, y = ~Dim.2, color = ~cluster, colors = c('#636EFA','#EF553B'), type = 'scatter', mode = 'markers') %>% 
  layout(
    legend=list(title=list(text='color')),
    plot_bgcolor = "#e5ecf6",
    xaxis = list(
      title = "0"),
    yaxis = list(
      title = "1")) 
for (i in 1:nrow(data_games.pca$var$coord)){
  fig <- fig %>%
    add_segments(x = 0, xend = 30*data_games.pca$var$coord[i,"Dim.1"], y = 0, yend = 30*data_games.pca$var$coord[i,"Dim.2"], line = list(color = 'black'),inherit = FALSE, showlegend = FALSE) %>%
    add_annotations(x=30*data_games.pca$var$coord[i,"Dim.1"], y=30*data_games.pca$var$coord[i,"Dim.2"], ax = 0, ay = 0,text = rownames(data_games.pca$var$coord)[i], xanchor = 'center', yanchor= 'bottom')
}

fig

# Sauvegarde du plot -> dans un objet HTML (particularité de plotly)
htmlwidgets::saveWidget(as_widget(fig), "graphe_cluster_projection_acp.html")
