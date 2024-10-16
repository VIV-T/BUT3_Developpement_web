<?php

namespace App\Entity;

use App\Repository\GenresRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GenresRepository::class)]
class Genres
{
    #[ORM\Id]
    #[ORM\OneToMany(targetEntity : LinkGamesGenres::class, mappedBy:"genres_id")]
    #[ORM\Column]
    private ?int $genres_id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column]
    private ?int $nb_games = null;

    #[ORM\Column(length: 255)]
    private ?string $total_revenue = null;

    #[ORM\Column]
    private ?int $avg_revenue = null;

    #[ORM\Column]
    private ?float $avg_price = null;

    #[ORM\Column]
    private ?float $avg_play_time = null;

    #[ORM\Column]
    private ?float $top5 = null;

    #[ORM\Column]
    private ?float $top25 = null;


    public function getGenresId(): ?int
    {
        return $this->genres_id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getNbGames(): ?int
    {
        return $this->nb_games;
    }

    public function setNbGames(int $nb_games): static
    {
        $this->nb_games = $nb_games;

        return $this;
    }

    public function getTotalRevenue(): ?string
    {
        return $this->total_revenue;
    }

    public function setTotalRevenue(string $total_revenue): static
    {
        $this->total_revenue = $total_revenue;

        return $this;
    }

    public function getAvgRevenue(): ?int
    {
        return $this->avg_revenue;
    }

    public function setAvgRevenue(int $avg_revenue): static
    {
        $this->avg_revenue = $avg_revenue;

        return $this;
    }

    public function getAvgPrice(): ?float
    {
        return $this->avg_price;
    }

    public function setAvgPrice(float $avg_price): static
    {
        $this->avg_price = $avg_price;

        return $this;
    }

    public function getAvgPlayTime(): ?float
    {
        return $this->avg_play_time;
    }

    public function setAvgPlayTime(float $avg_play_time): static
    {
        $this->avg_play_time = $avg_play_time;

        return $this;
    }

    public function getTop5(): ?float
    {
        return $this->top5;
    }

    public function setTop5(float $top5): static
    {
        $this->top5 = $top5;

        return $this;
    }

    public function getTop25(): ?float
    {
        return $this->top25;
    }

    public function setTop25(float $top25): static
    {
        $this->top25 = $top25;

        return $this;
    }
}