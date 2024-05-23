<?php

namespace App\Entity;

use App\Repository\FavoriRepository;
use App\Entity\Article;
use Doctrine\DBAL\Types\Types;
use App\Entity\Utilisateur;





use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoriRepository::class)]
class Favori
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\ManyToOne(inversedBy: 'favoris')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'favoris')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->date_creation = new \DateTimeImmutable();
    }






    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }


    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }




}
