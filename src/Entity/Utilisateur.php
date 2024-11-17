<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\EntityListeners(['App\EntityListener\UserListener'])]
#[UniqueEntity('email')]
#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    private ?string $plainPassword = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $code_postal = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date_naissance = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $pseudo;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photo_profil = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $bloque = null;

    #[ORM\OneToMany(targetEntity: HistoriqueConnexion::class, mappedBy: 'utilisateur')]
    private Collection $historiqueConnexions;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'utilisateur')]
    private Collection $messages;

    #[ORM\OneToMany(targetEntity: Panier::class, mappedBy: 'utilisateur')]
    private Collection $paniers;

    #[ORM\OneToMany(targetEntity: HistoriqueAchat::class, mappedBy: 'utilisateur')]
    private Collection $historiqueAchats;

    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'utilisateur')]
    private Collection $commentaires;

    #[ORM\ManyToMany(targetEntity: Article::class)]
    #[ORM\JoinTable(name: 'favoris_utilisateur')]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'article_id', referencedColumnName: 'id')]
    private Collection $favoris;

    #[ORM\OneToMany(targetEntity: MessageReaction::class, mappedBy: 'utilisateur')]
    private Collection $messageReactions;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $api_token = null;

    public function __construct()
    {
        $this->historiqueConnexions = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->paniers = new ArrayCollection();
        $this->historiqueAchats = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->photo_profil = null;
        $this->bloque = false;
        $this->favoris = new ArrayCollection();
        $this->messageReactions = new ArrayCollection();
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->code_postal;
    }

    public function setCodePostal(string $code_postal): static
    {
        $this->code_postal = $code_postal;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeImmutable
    {
        return $this->date_naissance;
    }

    public function setDateNaissance(\DateTimeImmutable $date_naissance): static
    {
        $this->date_naissance = $date_naissance;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getPhotoProfil(): ?string
    {
        return $this->photo_profil;
    }

    public function setPhotoProfil(?string $photo_profil): static
    {
        $this->photo_profil = $photo_profil;

        return $this;
    }

    public function isBloque(): ?bool
    {
        return $this->bloque;
    }

    public function setBloque(bool $bloque): static
    {
        $this->bloque = $bloque;

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

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

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
     * @return Collection<int, HistoriqueConnexion>
     */
    public function getHistoriqueConnexions(): Collection
    {
        return $this->historiqueConnexions;
    }

    public function addHistoriqueConnexion(HistoriqueConnexion $historiqueConnexion): static
    {
        if (!$this->historiqueConnexions->contains($historiqueConnexion)) {
            $this->historiqueConnexions->add($historiqueConnexion);
            $historiqueConnexion->setUtilisateur($this);
        }

        return $this;
    }

    public function removeHistoriqueConnexion(HistoriqueConnexion $historiqueConnexion): static
    {
        if ($this->historiqueConnexions->removeElement($historiqueConnexion)) {
            // set the owning side to null (unless already changed)
            if ($historiqueConnexion->getUtilisateur() === $this) {
                $historiqueConnexion->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setUtilisateur($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getUtilisateur() === $this) {
                $message->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Panier>
     */
    public function getPaniers(): Collection
    {
        return $this->paniers;
    }

    public function addPanier(Panier $panier): static
    {
        if (!$this->paniers->contains($panier)) {
            $this->paniers->add($panier);
            $panier->setUtilisateur($this);
        }

        return $this;
    }

    public function removePanier(Panier $panier): static
    {
        if ($this->paniers->removeElement($panier)) {
            // set the owning side to null (unless already changed)
            if ($panier->getUtilisateur() === $this) {
                $panier->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HistoriqueAchat>
     */
    public function getHistoriqueAchats(): Collection
    {
        return $this->historiqueAchats;
    }

    public function addHistoriqueAchat(HistoriqueAchat $historiqueAchat): static
    {
        if (!$this->historiqueAchats->contains($historiqueAchat)) {
            $this->historiqueAchats->add($historiqueAchat);
            $historiqueAchat->setUtilisateur($this);
        }

        return $this;
    }

    public function removeHistoriqueAchat(HistoriqueAchat $historiqueAchat): static
    {
        if ($this->historiqueAchats->removeElement($historiqueAchat)) {
            // set the owning side to null (unless already changed)
            if ($historiqueAchat->getUtilisateur() === $this) {
                $historiqueAchat->setUtilisateur(null);
            }
        }

        return $this;
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

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(Article $favori): static
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris->add($favori);
        }

        return $this;
    }

    public function removeFavori(Article $favori): static
    {
        $this->favoris->removeElement($favori);

        return $this;
    }

    public function fullName(): ?string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    /**
     * @return Collection<int, MessageReaction>
     */
    public function getMessageReactions(): Collection
    {
        return $this->messageReactions;
    }

    public function addMessageReaction(MessageReaction $messageReaction): static
    {
        if (!$this->messageReactions->contains($messageReaction)) {
            $this->messageReactions->add($messageReaction);
            $messageReaction->setUtilisateur($this);
        }

        return $this;
    }

    public function removeMessageReaction(MessageReaction $messageReaction): static
    {
        if ($this->messageReactions->removeElement($messageReaction)) {
            // set the owning side to null (unless already changed)
            if ($messageReaction->getUtilisateur() === $this) {
                $messageReaction->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->api_token;
    }

    public function setApiToken(?string $api_token): static
    {
        $this->api_token = $api_token;

        return $this;
    }
}
