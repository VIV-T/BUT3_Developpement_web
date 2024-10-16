CREATE TABLE games (
	AppID INT PRIMARY KEY, 
    gameName VARCHAR(100), 
    releaseDate DATE,
    releaseMonth VARCHAR(3),
    releaseYear INTEGER, 
    PEGI INT, 
    english_supported BOOL,
    header_img VARCHAR(150),
    notes VARCHAR(1000), 
    categories VARCHAR(1000), 
    publisherClass VARCHAR(20), 
    publishers VARCHAR(500), 
    developers VARCHAR(500),  
    systems VARCHAR(20), 
    copiesSold INT,  
    revenue FLOAT, 
    price FLOAT,
    avgPlaytime FLOAT, 
    reviewScore INT, 
    achievements INT, 
    recommandations INT
);


CREATE TABLE genres (
	Label VARCHAR(20),
	GenresID INT PRIMARY KEY, 
	nbGames INT, 
    totalRevenue VARCHAR(20), 
    avgRevenue INT, 
    avgPrice FLOAT,
	avgPlayTime FLOAT, 
    top5 FLOAT, 
    top25 FLOAT
);

CREATE TABLE link_games_genres (
	AppID INT, 
    GenresID INT, 
    FOREIGN KEY (AppID) REFERENCES games(AppID),
    FOREIGN KEY (GenresID) REFERENCES genres(GenresID)
);



# vérifier si d'autre champs sont intérressantes à garder.
CREATE TABLE years (
	year INT PRIMARY KEY, 
    gamesReleased INT, 
    totalRevenue INT, 
    avgRevenue INT, 
	avgPlayTime FLOAT, 
    avgPrice FLOAT
);