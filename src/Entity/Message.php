<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $date_message = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: true)]
    private ?self $reponseA = null;

    #[ORM\OneToMany(mappedBy: 'reponseA', targetEntity: self::class)]
    private Collection $reponses;

    public function __construct()
    {
        $this->date_message = new \DateTimeImmutable();
        $this->reponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getDateMessage(): ?\DateTimeImmutable
    {
        return $this->date_message;
    }

    public function setDateMessage(\DateTimeImmutable $date_message): static
    {
        $this->date_message = $date_message;
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

    public function getReponseA(): ?self
    {
        return $this->reponseA;
    }

    public function setReponseA(?self $reponseA): static
    {
        $this->reponseA = $reponseA;
        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Message $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setReponseA($this);
        }

        return $this;
    }

    public function removeReponse(Message $reponse): static
    {
        if ($this->reponses->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getReponseA() === $this) {
                $reponse->setReponseA(null);
            }
        }

        return $this;
    }
}
