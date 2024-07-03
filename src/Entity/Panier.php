<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $date_achat = null;

    #[ORM\Column(type: 'float')]
    private ?float $montant_total = null;

    #[ORM\OneToMany(targetEntity: HistoriqueAchat::class, mappedBy: 'panier')]
    private Collection $historiqueAchats;

    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'panier')]
    private Collection $ligneCommandes;

    public function __construct()
    {
        $this->date_achat = new \DateTimeImmutable();
        $this->historiqueAchats = new ArrayCollection();
        $this->ligneCommandes = new ArrayCollection();
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

    public function getDateAchat(): ?\DateTimeImmutable
    {
        return $this->date_achat;
    }

    public function setDateAchat(\DateTimeImmutable $date_achat): static
    {
        $this->date_achat = $date_achat;

        return $this;
    }

    public function getMontantTotal(): ?float
    {
        return $this->montant_total;
    }

    public function setMontantTotal(float $montant_total): static
    {
        $this->montant_total = $montant_total;

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
            $historiqueAchat->setPanier($this);
        }

        return $this;
    }

    public function removeHistoriqueAchat(HistoriqueAchat $historiqueAchat): static
    {
        if ($this->historiqueAchats->removeElement($historiqueAchat)) {
            // set the owning side to null (unless already changed)
            if ($historiqueAchat->getPanier() === $this) {
                $historiqueAchat->setPanier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LigneCommande>
     */
    public function getLigneCommandes(): Collection
    {
        return $this->ligneCommandes;
    }

    public function addLigneCommande(LigneCommande $ligneCommande): static
    {
        if (!$this->ligneCommandes->contains($ligneCommande)) {
            $this->ligneCommandes->add($ligneCommande);
            $ligneCommande->setPanier($this);
        }

        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligneCommande): static
    {
        if ($this->ligneCommandes->removeElement($ligneCommande)) {
            // set the owning side to null (unless already changed)
            if ($ligneCommande->getPanier() === $this) {
                $ligneCommande->setPanier(null);
            }
        }

        return $this;
    }
}
