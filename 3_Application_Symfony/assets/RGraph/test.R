getwd()


# Dérouter la sortie standard vers le fichier
sink("test_R.txt")

# Écrire du texte dans le fichier
print(getwd())

# Remettre la sortie standard à son emplacement initial
sink()