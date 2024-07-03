<?php

    namespace App\Event;

use App\Entity\Utilisateur;
use Symfony\Contracts\EventDispatcher\Event;

    class UserLoggedInEvent extends Event
    {
        public const NAME = 'user.logged_in';

        public function __construct(protected ?Utilisateur $utilisateur)
        {
            
        }

        public function getUtilisateur(): ?Utilisateur
        {
            return $this->utilisateur;
        }
    }