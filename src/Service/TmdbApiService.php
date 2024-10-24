<?php

namespace App\Service;

// Importation des classes nécessaires pour la gestion des requêtes HTTP et la langue
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbApiService
{
    // Constructeur qui initialise les dépendances nécessaires pour le service
    public function __construct(private HttpClientInterface $httpClient, private RequestStack $requestStack, private string $apiKey, private string $apiUrl)
    {
        // Les dépendances sont injectées via le constructeur pour faciliter les tests et la modularité.
    }

    // Méthode pour récupérer les films populaires
    public function getPopularMovies(): array
    {
        return $this->fetchFromTmdb('/movie/popular'); // Appelle la méthode privée pour effectuer la requête
    }

    // Méthode pour récupérer les séries TV populaires
    public function getPopularTvShows(): array
    {
        return $this->fetchFromTmdb('/tv/popular'); // Utilise la même logique que pour les films
    }

    // Méthode pour récupérer les acteurs populaires
    public function getPopularActors(): array
    {
        return $this->fetchFromTmdb('/person/popular'); // Requête pour obtenir des acteurs
    }

    // Méthode privée qui exécute une requête à l'API TMDb
    private function fetchFromTmdb(string $endpoint): array
    {
        // Récupère la locale de la requête actuelle
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        // Envoi de la requête HTTP GET à l'API TMDb avec l'endpoint donné
        $response = $this->httpClient->request('GET', $this->apiUrl . $endpoint, [
            'query' => [
                'api_key' => $this->apiKey, // Ajoute la clé API pour l'authentification
                'language' => $locale, // Définit la langue selon la locale de la requête
            ]
        ]);

        // Vérifie si la réponse a un code de statut différent de 200 (OK)
        if ($response->getStatusCode() !== 200) {
            // Lance une exception si la récupération des données échoue
            throw new \Exception('Failed to fetch data from TMDb API');
        }

        // Transforme la réponse en tableau
        $data = $response->toArray();

        // Retourne les résultats ou un tableau vide si 'results' n'est pas présent
        return $data['results'] ?? [];
    }
}