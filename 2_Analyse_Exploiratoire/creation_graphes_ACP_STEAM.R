##### Creation graphe ACP - Projet STEAM #####

## Import des packages
library(DBI)
library(ggplot2)
library(cluster)
library(fdm2id)
library(plotly)
library(htmlwidgets)
library(rrepast)

###### Code a lancer que lorsque l'execution provient de Symfony
## Initialisation du dossier d'output a partir de la var 
# d'environement passee en parametre depuis le script Symfony
# exemple d'utilisation :
# test = Sys.getenv("R_HOME")
# test
#output_dir = Sys.getenv("OUTPUT_DIRECTORY")
# set the output dir
#setOutputDir(output_dir)
#setwd()

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
tables <- dbListTables(connexion)


# Display structure of tables
str(tables)


# Import the accounts table from mydb
games <- dbReadTable(connexion, "games")


data_games = as.data.frame(games)


## R?alisation de l'ACP 

# mettre que les donn?es quantitative dans l'ACP
# variables utilis?es :
#  - copiesSold
#  - revenue
#  - recommandations

data_games.pca = PCA(data_games[,c(15:19,21)])

# enregistrement des graphes de l'ACP : 
# - cercles des corr?lations
# - projection sur le premier plan factoriel
# - renvoyer aussi l'inertie totale du premier plan factoriel
graphe_ACP_cercle_cor = plot(data_games.pca, type = "cor")
ggsave("graphe_ACP_cercle_cor.png", plot=graphe_ACP_cercle_cor)

graphe_ACP_projection_points = plot(data_games.pca)
ggsave("graphe_ACP_projection_points.png", plot=graphe_ACP_projection_points)


# inertie
inertie_premier_plan_factoriel = data_games.pca$eig[2,3]


## Application du K-MEANS sur les coordonn?es de l'ACP

# détermination du nombre de cluster optimal
nb_cluster_opti = fdm2id::kmeans.getk(data_games.pca$ind$coor)

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



## Partie experimentale :

data_games.pca$ind$coord=as.matrix(data_games_clust)


data_games_clust =data_games_clust[,c(1,2,7)]





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


# Sauvegarde du plot -> dans un objet HTML (particularité de plotly)
htmlwidgets::saveWidget(as_widget(fig), "graphe_cluster_projection_acp.png.html")
