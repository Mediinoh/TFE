<?php

namespace App\Entity;

use App\Repository\HistoriqueConnexionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueConnexionRepository::class)]
class HistoriqueConnexion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'historiqueConnexions')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $date_connexion = null;

    public function __construct()
    {
        $this->date_connexion = new \DateTimeImmutable();    
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateConnexion(): ?\DateTimeImmutable
    {
        return $this->date_connexion;
    }

    public function setDateConnexion(\DateTimeImmutable $date_connexion): static
    {
        $this->date_connexion = $date_connexion;

        return $this;
    }
}
