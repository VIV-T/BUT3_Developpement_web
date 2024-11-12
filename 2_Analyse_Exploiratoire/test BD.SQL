SELECT * FROM e1735u_Projet_Steam_Doctrine.games
;


select sum(revenue)/count(*) as revenus_per_game
FROM e1735u_Projet_Steam_Doctrine.games;

select count(*) 
FROM e1735u_Projet_Steam_Doctrine.games;

select sum(revenue), sum(copies_sold)
FROM e1735u_Projet_Steam_Doctrine.games;

select 
	sum(revenue) as cumul_revenue_top100,
	sum(copies_sold) as cumul_copiesSold_top100
from (
select *
FROM e1735u_Projet_Steam_Doctrine.games
order by revenue DESC LIMIT 100
) as temp ;

select 
	sum(revenue) as cumul_revenue_top1000, 
	sum(copies_sold) as cumul_copiesSold_top1000
from (
select *
FROM e1735u_Projet_Steam_Doctrine.games
order by revenue DESC LIMIT 1000
) as temp ;



select count(*)
from games
where release_year = 2024 and publisher_class = "Hobbyist";