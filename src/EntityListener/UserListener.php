<?php

    namespace App\EntityListener;

    // Importe les classes nécessaires
    use App\Entity\Utilisateur;
    use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

    // La classe UserListener écoute les évenements de cycle de vie de l'entité Utilisateur.
    class UserListener
    {

        // Constructeur de la classe injectant le service de hachage de mot de passe
        public function __construct(private UserPasswordHasherInterface $hasher)
        {
            
        }

        // Méthode appelée avant la création d'un nouvel Utilisateur en base de données
        public function prePersist(Utilisateur $utilisateur)
        {
            $this->encodePassword($utilisateur);
        }

        // Méthode appelée avant la mise à jour d'un Utilisateur existant
        public function preUpdate(Utilisateur $utilisateur)
        {
            $this->encodePassword($utilisateur);
        }

        /**
        * Encode le mot de passe de l'utilisateur s'il est défini en clair
        *
        * @param Utilisateur $utilisateur L'entité Utilisateur à traiter
        * @return void
        */
        public function encodePassword(Utilisateur $utilisateur)
        {
            // Vérifie si un mot de passe en clair est fourni
            if (is_null($utilisateur->getPlainPassword())) {
                return;
            }

            // Hache le mot de passe en clair et met ç jour le champ 'password'
            $utilisateur->setPassword(
                $this->hasher->hashPassword($utilisateur, $utilisateur->getPlainPassword())
            );

            // Efface le mot de passe en clair pour ne pas le conserver dans l'entité
            $utilisateur->setPlainPassword(null);
        }
    }

?>