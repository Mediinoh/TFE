<?php

namespace App\Entity;

use App\Repository\MessageReactionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageReactionRepository::class)]
class MessageReaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Message $message = null;

    #[ORM\ManyToOne(inversedBy: 'messageReactions')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: 'string', columnDefinition: "ENUM('like', 'dislike')")]
    private ?string $reaction_type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getUtilisateur(): ?utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getReactionType(): ?string
    {
        return $this->reaction_type;
    }

    public function setReactionType(string $reaction_type): static
    {
        $this->reaction_type = $reaction_type;

        return $this;
    }
}
