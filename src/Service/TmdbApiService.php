<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbApiService
{
    public function __construct(private HttpClientInterface $httpClient, private string $apiKey, private string $apiUrl)
    {
        
    }

    public function getPopularMovies(): array
    {
        return $this->fetchFromTmdb('/movie/popular');
    }

    public function getPopularTvShows(): array
    {
        return $this->fetchFromTmdb('/tv/popular');
    }

    public function getPopularActors(): array
    {
        return $this->fetchFromTmdb('/person/popular');
    }

    private function fetchFromTmdb(string $endpoint): array
    {
        $response = $this->httpClient->request('GET', $this->apiUrl . $endpoint, [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => 'fr',
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to fetch data from TMDb API');
        }

        $data = $response->toArray();

        return $data['results'] ?? [];
    }
}