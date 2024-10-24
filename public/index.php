<?php

// Utilisation de la classe Kernel de l'application
use App\Kernel;

// Chargement du fichier d'autoload généré par Composer pour inclure toutes les dépendances
require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// Retourne une fonction anonyme qui crée une instance de la classe Kernel
return function (array $context) {
    // Crée et retourne une nouvelle instance de Kernel en passant l'environnement et le mode debug à partir du tableau de contexte
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
