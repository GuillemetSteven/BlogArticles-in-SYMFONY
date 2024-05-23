<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]  // Si l'utilisateur est supprimé, le commentaire reste mais 'utilisateur_id' devient NULL
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Article $article = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'reponses')]
    private ?Commentaire $commentaireParent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'commentaireParent', cascade: ['persist', 'remove'])]
    private Collection $reponses;

    #[ORM\OneToMany(targetEntity: Reaction::class, mappedBy: 'commentaire')]
    private Collection $reactions;

    public function __construct()
    {
        $this->date_creation = new \DateTimeImmutable();
        $this->reponses = new ArrayCollection();
        $this->reactions = new ArrayCollection();
    }

    // Getters and setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getCommentaireParent(): ?self
    {
        return $this->commentaireParent;
    }

    public function setCommentaireParent(?self $commentaireParent): self
    {
        $this->commentaireParent = $commentaireParent;
        return $this;
    }

    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(self $reponse): self
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses[] = $reponse;
            $reponse->setCommentaireParent($this);
        }
        return $this;
    }

    public function removeReponse(self $reponse): self
    {
        if ($this->reponses->removeElement($reponse) && $reponse->getCommentaireParent() === $this) {
            $reponse->setCommentaireParent(null);
        }
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

    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    public function addReaction(Reaction $reaction): self
    {
        if (!$this->reactions->contains($reaction)) {
            $this->reactions[] = $reaction;
            $reaction->setCommentaire($this);
        }
        return $this;
    }

    public function removeReaction(Reaction $reaction): self
    {
        if ($this->reactions->removeElement($reaction) && $reaction->getCommentaire() === $this) {
            $reaction->setCommentaire(null);
        }
        return $this;
    }
}
