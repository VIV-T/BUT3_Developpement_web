CREATE VIEW graph_year_month_genres AS (
	SELECT Label, releaseYear, releaseMonth, SUM(copiesSold) AS sommeCopiesSold
	FROM games 
		JOIN link_games_genres USING (AppID)
		JOIN genres USING(GenresID)
	GROUP BY GenresID, releaseYear, releaseMonth WITH ROLLUP
);

CREATE VIEW graph_year_genres AS (
	SELECT Label, releaseYear, SUM(copiesSold) AS sommeCopiesSold
	FROM games 
		JOIN link_games_genres USING (AppID)
		JOIN genres USING(GenresID)
	GROUP BY GenresID, releaseYear
);

CREATE VIEW graph_month_genres AS (
	SELECT Label, releaseMonth, SUM(copiesSold) AS sommeCopiesSold
	FROM games 
		JOIN link_games_genres USING (AppID)
		JOIN genres USING(GenresID)
	GROUP BY GenresID, releaseMonth
);

CREATE VIEW graph_revenue_PEGI_genres AS (
SELECT PEGI, Label, revenue, SUM(revenue) AS sommeRevenue
FROM games 
	JOIN link_games_genres USING (AppID)
	JOIN genres USING(GenresID)
GROUP BY PEGI, Label WITH ROLLUP
);


CREATE VIEW graph_genres_5_dim AS (
SELECT 
	Label, 
	COUNT(*) AS nbGames, 
    SUM(revenue) AS sommeRevenue, 
    SUM(copiesSold) AS sommeCopiesSold, 
    AVG(reviewScore) AS averageReviewScore
FROM games 
	JOIN link_games_genres USING (AppID)
	JOIN genres USING(GenresID)
GROUP BY Label 
);

SELECT 
	Label, 
	COUNT(*) AS nbGames, 
    SUM(revenue) AS sommeRevenue, 
    SUM(copiesSold) AS sommeCopiesSold, 
    AVG(reviewScore) AS averageReviewScore
FROM games 
	JOIN link_games_genres USING (AppID)
	JOIN genres USING(GenresID)
GROUP BY Label ;