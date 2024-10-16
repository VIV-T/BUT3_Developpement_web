<?php

namespace App\Entity;

use App\Repository\GamesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GamesRepository::class)]
class Games
{
    #[ORM\Id]
    #[ORM\OneToMany(targetEntity : LinkGamesGenres::class, mappedBy:"app_id")]
    #[ORM\Column]
    private ?int $app_id = null;

    #[ORM\Column(length: 255)]
    private ?string $game_name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $release_date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $release_month = null;

    #[ORM\Column]
    private ?int $release_year = null;

    #[ORM\Column(nullable: true)]
    private ?int $PEGI = null;

    #[ORM\Column(length: 255)]
    private ?bool $english_supported = null;

    #[ORM\Column(length: 255)]
    private ?string $header_img = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $categories = null;

    #[ORM\Column(length: 255)]
    private ?string $publisher_class = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $publishers = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $developers = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $systems = null;

    #[ORM\Column]
    private ?int $copies_sold = null;

    #[ORM\Column(nullable: true)]
    private ?float $revenue = null;

    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[ORM\Column(nullable: true)]
    private ?float $avg_play_time = null;

    #[ORM\Column(nullable: true)]
    private ?int $review_score = null;

    #[ORM\Column(nullable: true)]
    private ?int $achievements = null;

    #[ORM\Column(nullable: true)]
    private ?int $recommandations = null;


    public function getAppId(): ?int
    {
        return $this->app_id;
    }

    public function getGameName(): ?string
    {
        return $this->game_name;
    }

    public function setGameName(string $game_name): static
    {
        $this->game_name = $game_name;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->release_date;
    }

    public function setReleaseDate(?\DateTimeInterface $release_date): static
    {
        $this->release_date = $release_date;

        return $this;
    }

    public function getReleaseMonth(): ?string
    {
        return $this->release_month;
    }

    public function setReleaseMonth(?string $release_month): static
    {
        $this->release_month = $release_month;

        return $this;
    }

    public function getReleaseYear(): ?int
    {
        return $this->release_year;
    }

    public function setReleaseYear(int $release_year): static
    {
        $this->release_year = $release_year;

        return $this;
    }

    public function getPEGI(): ?int
    {
        return $this->PEGI;
    }

    public function setPEGI(?int $PEGI): static
    {
        $this->PEGI = $PEGI;

        return $this;
    }

    public function getEnglishSupported(): ?string
    {
        return $this->english_supported;
    }

    public function setEnglishSupported(string $english_supported): static
    {
        $this->english_supported = $english_supported;

        return $this;
    }

    public function getHeaderImg(): ?string
    {
        return $this->header_img;
    }

    public function setHeaderImg(string $header_img): static
    {
        $this->header_img = $header_img;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCategories(): ?string
    {
        return $this->categories;
    }

    public function setCategories(?string $categories): static
    {
        $this->categories = $categories;

        return $this;
    }

    public function getPublisherClass(): ?string
    {
        return $this->publisher_class;
    }

    public function setPublisherClass(string $publisher_class): static
    {
        $this->publisher_class = $publisher_class;

        return $this;
    }

    public function getPublishers(): ?string
    {
        return $this->publishers;
    }

    public function setPublishers(?string $publishers): static
    {
        $this->publishers = $publishers;

        return $this;
    }

    public function getDevelopers(): ?string
    {
        return $this->developers;
    }

    public function setDevelopers(?string $developers): static
    {
        $this->developers = $developers;

        return $this;
    }

    public function getSystems(): ?string
    {
        return $this->systems;
    }

    public function setSystems(?string $systems): static
    {
        $this->systems = $systems;

        return $this;
    }

    public function getCopiesSold(): ?int
    {
        return $this->copies_sold;
    }

    public function setCopiesSold(int $copies_sold): static
    {
        $this->copies_sold = $copies_sold;

        return $this;
    }

    public function getRevenue(): ?float
    {
        return $this->revenue;
    }

    public function setRevenue(?float $revenue): static
    {
        $this->revenue = $revenue;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getAvgPlayTime(): ?float
    {
        return $this->avg_play_time;
    }

    public function setAvgPlayTime(?float $avg_play_time): static
    {
        $this->avg_play_time = $avg_play_time;

        return $this;
    }

    public function getReviewScore(): ?int
    {
        return $this->review_score;
    }

    public function setReviewScore(?int $review_score): static
    {
        $this->review_score = $review_score;

        return $this;
    }

    public function getAchievements(): ?int
    {
        return $this->achievements;
    }

    public function setAchievements(?int $achievements): static
    {
        $this->achievements = $achievements;

        return $this;
    }

    public function getRecommandations(): ?int
    {
        return $this->recommandations;
    }

    public function setRecommandations(?int $recommandations): static
    {
        $this->recommandations = $recommandations;

        return $this;
    }
}