CREATE TABLE dashboard (
app_id INT NOT NULL, 
game_name VARCHAR(255) NOT NULL, 
release_date DATE DEFAULT NULL, 
release_month VARCHAR(255) DEFAULT NULL, 
release_year INT NOT NULL, 
pegi INT DEFAULT NULL, 
english_supported TINYINT(1) NOT NULL, 
header_img VARCHAR(255) NOT NULL, 
notes VARCHAR(1000) DEFAULT NULL, 
categories VARCHAR(1000) DEFAULT NULL, 
publisher_class VARCHAR(255) NOT NULL, 
publishers VARCHAR(500) DEFAULT NULL, 
developers VARCHAR(500) DEFAULT NULL, 
systems VARCHAR(255) DEFAULT NULL, 
copies_sold INT NOT NULL, 
revenue DOUBLE PRECISION DEFAULT NULL, 
price DOUBLE PRECISION DEFAULT NULL, 
avg_play_time DOUBLE PRECISION DEFAULT NULL, 
review_score INT DEFAULT NULL, 
achievements INT DEFAULT NULL, 
recommandations INT DEFAULT NULL, 
PRIMARY KEY(app_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
;