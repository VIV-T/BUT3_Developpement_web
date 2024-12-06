##### Pour initialiser la sortie des graphes au bon endroit : 
# etant donner que le code est executer depuis la ligne de cmd, on recupere les arguments donne a la cmd Rscript.exe
# L'argument recupere est le filePath du fichier courant
# On le retravail pour obtenir le path du directory avant de setwd(la path en question)
# Il faut modifier le fichier de sortie pour eviter d'avoir les graphes dans le dossier de la cmd d'execution.
library(stringr)
library(rmarkdown)


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

setwd(absolute_path_dir)

tryCatch(
  setwd(absolute_path_dir), 
  error = function(e) {  
    print("error setting working directory")
  }
)

  

