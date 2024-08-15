<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbApiService
{
    public function __construct(private HttpClientInterface $httpClient, private RequestStack $requestStack, private string $apiKey, private string $apiUrl)
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
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        $response = $this->httpClient->request('GET', $this->apiUrl . $endpoint, [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => $locale,
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to fetch data from TMDb API');
        }

        $data = $response->toArray();

        return $data['results'] ?? [];
    }
}