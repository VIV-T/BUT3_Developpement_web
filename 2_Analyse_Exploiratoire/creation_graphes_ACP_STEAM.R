##### Creation graphe ACP - Projet STEAM #####

## Import des packages
library(DBI)
library(ggplot2)
library(cluster)
library(fdm2id)


## Connection à la base de données 

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


## Réalisation de l'ACP 

# mettre que les données quantitative dans l'ACP
# variables utilisées :
#  - copiesSold
#  - revenue
#  - recommandations

data_games.pca = PCA(data_games[,c(15:19,21)])

# enregistrement des graphes de l'ACP : 
# - cercles des corrélations
# - projection sur le premier plan factoriel
# - renvoyer aussi l'inertie totale du premier plan factoriel
graphe_ACP_cercle_cor = plot(data_games.pca, type = "cor")
graphe_ACP_projection_points = plot(data_games.pca)

# inertie
inertie_premier_plan_factoriel = data_games.pca$eig[2,3]


## Application du K-MEANS sur les coordonnées de l'ACP

# détermination du nombre de cluster optimal
nb_cluster_opti = fdm2id::kmeans.getk(data_games.pca$ind$coor)

# application du kmeans
data_games_ACP_kmeans = fdm2id::KMEANS(data_games.pca$ind$coord, k=nb_cluster_opti)


# recupération individuellement des données des clusters 

# creation d'un dataframe vide pour accueillir les données de la table games 
# avec leur nouvelle var désignant leur cluster associé.
data_games_clust = data.frame(Dim1=numeric(), Dim2=numeric(), Dim3=numeric(), Dim4=numeric(), Dim5=numeric(), Dim6=numeric(), cluster=factor())

for (i in 1:nb_cluster_opti) {
  # récuperation de tous les index de la table de données du premier cluster
  index_data_clust = as.vector(which(data_games_ACP_kmeans$cluster == i))
  
  # extraction des données à partir de l'index et de la table originale.
  data_games_clust_temp = as.data.frame(data_games.pca$ind$coord[index_data_clust,])
  
  # ajout de la colonne associée au cluster
  data_games_clust_temp[, "cluster"] = i
  
  # fusion avec le dataset de resultat.
  data_games_clust = rbind(data_games_clust, data_games_clust_temp)
}



## Partie experimentale :

data_games.pca$ind$coord=as.matrix(data_games_clust)


graphe_ACP_projection_points = plot.PCA(data_games.pca$ind$coord, axes = c(1,2), col.ind=data_games.pca$ind$coord[,"cluster"])


data_games_clust =data_games_clust[,c(1,2,7)]

## creation d'un nouveau graphe coloré en fonction du cluster déterminé avec KMEANS.
graphe_ACP_projection_points_colored = plot(x = data_games_clust$Dim.1, y=data_games_clust$Dim.2, col = data_games_clust$cluster)



autoplot()
autoplot(data_games_clust, frame = TRUE, frame.type = 'norm')




library(prcomp)
library(ggfortify)

pca_res <- prcomp(data_games[,c(15:19,21)], scale = TRUE)
autoplot(pca_res, data = data_games_clust, colour = "cluster",  loadings = TRUE, loadings.label = TRUE)




library(plotly)



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
    add_segments(x = 0, xend = 20*data_games.pca$var$coord[i,"Dim.1"], y = 0, yend = 20*data_games.pca$var$coord[i,"Dim.2"], line = list(color = 'black'),inherit = FALSE, showlegend = FALSE) %>%
    add_annotations(x=20*data_games.pca$var$coord[i,"Dim.1"], y=20*data_games.pca$var$coord[i,"Dim.2"], ax = 0, ay = 0,text = rownames(data_games.pca$var$coord)[i], xanchor = 'center', yanchor= 'bottom')
}

fig
