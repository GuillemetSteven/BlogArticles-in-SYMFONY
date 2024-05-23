<?php

namespace App\Entity;

use App\Repository\ReactionRepository;
use App\Entity\Utilisateur;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReactionRepository::class)]
class Reaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[ORM\ManyToOne(inversedBy: 'reactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;


    /* Vous devrez ajouter une relation ManyToOne avec Commentaire si cela est nécessaire pour votre application.
    / Par exemple : */

    #[ORM\ManyToOne(inversedBy: 'reactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commentaire $commentaire = null;

    #[ORM\ManyToOne(inversedBy: 'reactions')]
    #[ORM\JoinColumn(nullable: false)] // Ceci garantit qu'une réaction a toujours un émoji associé.
    private ?Emoji $emoji;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmoji(): ?Emoji
    {
        return $this->emoji;
    }

    public function setEmoji(Emoji $emoji): self
    {
        $this->emoji = $emoji;
        return $this;
    }

    public function getCommentaire(): ?Commentaire
    {
        return $this->commentaire;
    }

    public function setCommentaire(?Commentaire $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }
}
