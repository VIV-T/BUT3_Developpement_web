<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241015192722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE games (app_id INT NOT NULL, game_name VARCHAR(255) NOT NULL, release_date DATE DEFAULT NULL, release_month VARCHAR(255) DEFAULT NULL, release_year INT NOT NULL, pegi INT DEFAULT NULL, english_supported TINYINT(1) NOT NULL, header_img VARCHAR(255) NOT NULL, notes VARCHAR(1000) DEFAULT NULL, categories VARCHAR(1000) DEFAULT NULL, publisher_class VARCHAR(255) NOT NULL, publishers VARCHAR(500) DEFAULT NULL, developers VARCHAR(500) DEFAULT NULL, systems VARCHAR(255) DEFAULT NULL, copies_sold INT NOT NULL, revenue DOUBLE PRECISION DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, avg_play_time DOUBLE PRECISION DEFAULT NULL, review_score INT DEFAULT NULL, achievements INT DEFAULT NULL, recommandations INT DEFAULT NULL, PRIMARY KEY(app_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE genres (genres_id INT NOT NULL, label VARCHAR(255) NOT NULL, nb_games INT NOT NULL, total_revenue VARCHAR(255) NOT NULL, avg_revenue INT NOT NULL, avg_price DOUBLE PRECISION NOT NULL, avg_play_time DOUBLE PRECISION NOT NULL, top5 DOUBLE PRECISION NOT NULL, top25 DOUBLE PRECISION NOT NULL, PRIMARY KEY(genres_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE link_games_genres (id INT AUTO_INCREMENT NOT NULL, app_id INT NOT NULL, genres_id INT NOT NULL, INDEX IDX_986C8A617987212D (app_id), INDEX IDX_986C8A616A3B2603 (genres_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE link_games_genres ADD CONSTRAINT FK_986C8A617987212D FOREIGN KEY (app_id) REFERENCES games (app_id)');
        $this->addSql('ALTER TABLE link_games_genres ADD CONSTRAINT FK_986C8A616A3B2603 FOREIGN KEY (genres_id) REFERENCES genres (genres_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE link_games_genres DROP FOREIGN KEY FK_986C8A617987212D');
        $this->addSql('ALTER TABLE link_games_genres DROP FOREIGN KEY FK_986C8A616A3B2603');
        $this->addSql('DROP TABLE games');
        $this->addSql('DROP TABLE genres');
        $this->addSql('DROP TABLE link_games_genres');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
