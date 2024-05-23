<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;



#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column]
private ?int $id = null;

#[ORM\Column(length: 255)]
private ?string $email = null;

#[ORM\Column]
private array $roles = [];

#[ORM\Column(length: 255)]
private ?string $password = null;




    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'utilisateur', cascade: ['remove'])]
    private Collection $commentaires;

    #[ORM\OneToMany(targetEntity: Reaction::class, mappedBy: 'utilisateur', cascade: ['remove'])]
    private Collection $reactions;

    #[ORM\OneToMany(targetEntity: Favori::class, mappedBy: 'utilisateur', cascade: ['remove'])]
    private Collection $favoris;


public function __construct()
{
$this->commentaires = new ArrayCollection();
$this->reactions = new ArrayCollection();
$this->favoris = new ArrayCollection();
}

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $username = null;

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $userProfileImage = null;

    public function getUserProfileImage(): ?string
    {
        return $this->userProfileImage;
    }

    public function setUserProfileImage(?string $userProfileImage): self
    {
        $this->userProfileImage = $userProfileImage;
        return $this;
    }

public function getId(): ?int
{
return $this->id;
}

public function getEmail(): ?string
{
return $this->email;
}

public function setEmail(string $email): static
{
$this->email = $email;

return $this;
}

/**
* A visual identifier that represents this user.
*
* @see UserInterface
*/
public function getUserIdentifier(): string
{
return (string) $this->email;
}

/**
* @see UserInterface
*/
public function getRoles(): array
{
$roles = $this->roles;
// guarantee every user at least has ROLE_USER
$roles[] = 'ROLE_USER';

return array_unique($roles);
}

public function setRoles(array $roles): self
{
    $this->roles = $roles;
    return $this;
}

public function addRole(string $role): self
{
    if (!in_array($role, $this->roles, true))
    {
        $this->roles[] = $role;
    }
        return $this;
}

    public function removeRole(string $role): self
    {
        if (in_array($role, $this->roles, true)) {
            $key = array_search($role, $this->roles);
            if (false !== $key) {
                unset($this->roles[$key]);
            }
        }

        return $this;
    }





/**
* @see PasswordAuthenticatedUserInterface
*/
public function getPassword(): string
{
return $this->password;
}

public function setPassword(string $password): static
{
$this->password = $password;

return $this;
}

/**
* @see UserInterface
*/
public function eraseCredentials(): void
{
// If you store any temporary, sensitive data on the user, clear it here
// $this->plainPassword = null;
}

/**
* @return Collection<int, Commentaire>
*/
public function getCommentaires(): Collection
{
return $this->commentaires;
}



public function addCommentaire(Commentaire $commentaire): static
{
if (!$this->commentaires->contains($commentaire)) {
$this->commentaires->add($commentaire);
$commentaire->setUtilisateur($this);
}

return $this;
}

public function removeCommentaire(Commentaire $commentaire): static
{
if ($this->commentaires->removeElement($commentaire)) {
// set the owning side to null (unless already changed)
if ($commentaire->getUtilisateur() === $this) {
$commentaire->setUtilisateur(null);
}
}

return $this;
}

/**
* @return Collection<int, Reaction>
*/
public function getReactions(): Collection
{
return $this->reactions;
}

public function addReaction(Reaction $reaction): static
{
if (!$this->reactions->contains($reaction)) {
$this->reactions->add($reaction);
$reaction->setUtilisateur($this);
}

return $this;
}

public function removeReaction(Reaction $reaction): static
{
if ($this->reactions->removeElement($reaction)) {
// set the owning side to null (unless already changed)
if ($reaction->getUtilisateur() === $this) {
$reaction->setUtilisateur(null);
}
}

return $this;
}

/**
* @return Collection<int, Favori>
*/
public function getFavoris(): Collection
{
return $this->favoris;
}

public function addFavori(Favori $favori): static
{
if (!$this->favoris->contains($favori)) {
$this->favoris->add($favori);
$favori->setUtilisateur($this);
}

return $this;
}

public function removeFavori(Favori $favori): static
{
if ($this->favoris->removeElement($favori)) {
// set the owning side to null (unless already changed)
if ($favori->getUtilisateur() === $this) {
$favori->setUtilisateur(null);
}
}

return $this;
}

/**
* @var string|null Le mot de passe en clair
*/
private ?string $plainPassword = null;

public function getPlainPassword(): ?string
{
return $this->plainPassword;
}

public function setPlainPassword(string $plainPassword): self
{
$this->plainPassword = $plainPassword;
return $this;
}







}
