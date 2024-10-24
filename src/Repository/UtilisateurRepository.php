<?php

namespace App\Repository;

// Importation des classes nécessaires pour le repository et la gestion des utilisateurs
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Utilisateur>
* @implements PasswordUpgraderInterface<Utilisateur>
 *
 * @method Utilisateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Utilisateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Utilisateur[]    findAll()
 * @method Utilisateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    // Constructeur qui appelle le constructeur parent avec le registre d'entités et la classe 
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Utilisé pour mettre à jour (rehacher) le mot de passe de l'utilisateur automatiquement au fil du temps.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        // Vérifie que l'utilisateur est une instance de Utilisateur
        if (!$user instanceof Utilisateur) {
            // Lance une exception si l'utilisateur n'est pas supporté
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        // Met à jour le mot de passe l'utilisateur
        $user->setPassword($newHashedPassword);
        // Persiste les modifications de l'utilisateur dans l'entité
        $this->getEntityManager()->persist($user);
        // Enregistre les modifications dans la base de données
        $this->getEntityManager()->flush();
    }
}
