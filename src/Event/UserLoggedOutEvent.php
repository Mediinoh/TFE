<?php

namespace App\Event;

// Importe les classes nécessaires
use App\Entity\Utilisateur;
use Symfony\Contracts\EventDispatcher\Event;

// Déclare la classe UserLoggedOutEvent pour réprésenter l'événement de déconnexion d'un utilisateur
class UserLoggedOutEvent extends Event
{
    // Déclaration d'une constante pour identifier cet événement
    public const NAME = 'user.logged_out';

    // Constructeur de la classe qui initialise l'utilisateur concerné par l'événement
    public function __construct(protected ?Utilisateur $utilisateur)
    {
        
    }

    // Méthode publique pour récupérer l'utilisateur concerné par l'évenement
}
