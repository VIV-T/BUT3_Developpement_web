<?php

namespace App\Entity;

use App\Repository\LinkGamesGenresRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LinkGamesGenresRepository::class)]
class LinkGamesGenres
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity : Games::class, inversedBy:"app_id")]
    #[ORM\JoinColumn(nullable: false, referencedColumnName : "app_id")]
    private ?Games $app = null;

    #[ORM\ManyToOne(targetEntity : Genres::class, inversedBy:"genres_id")]
    #[ORM\JoinColumn(nullable: false, referencedColumnName : "genres_id")]
    private ?Genres $genres = null;


    public function getAppId(): ?Games
    {
        return $this->app_id;
    }

    public function getGenresId(): ?Genres
    {
        return $this->genres_id;
    }
}