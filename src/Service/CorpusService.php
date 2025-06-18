<?php

namespace App\Service;

use App\Entity\Project;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CorpusService
{
    private Client $client;
    private string $corpusUrl;
    private string $corpusUsername;
    private string $corpusPassword;

    public function __construct(ParameterBagInterface $params)
    {
        $this->client = new Client(); // Tu peux aussi l'injecter via le container
        $this->corpusUrl = $params->get('corpusUrl');
        $this->corpusUsername = $params->get('corpusUsername');
        $this->corpusPassword = $params->get('corpusPassword');
    }

    public function indexCorpus(Project $project, string $projectPath, string $filePath): bool
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Fichier introuvable: $filePath");
        }

        $sourceUrl = str_replace($projectPath, '', $filePath);
        $response = $this->client->request('POST', $this->corpusUrl.'/api/v1/index', [
            'auth' => [$this->corpusUsername, $this->corpusPassword],
            'multipart' => [
                [
                    'name' => 'collection',
                    'contents' => $project->getTitle(),
                ],
                [
                    'name' => 'file',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => basename($filePath),
                    'headers' => ['Content-Type' => 'text/html'],
                ],
                [
                    'name' => 'source',
                    'contents' => $sourceUrl,
                ],
            ],
        ]);

        return 200 === $response->getStatusCode();
    }

    public function search(Project $project, string $query): array
    {
        try {
            $response = $this->client->request('GET', $this->corpusUrl.'/api/v1/search', [
                'auth' => [$this->corpusUsername, $this->corpusPassword],
                'query' => [
                    'query' => $query,
                    'collection' => $project->getTitle(),
                ],
                'headers' => [
                    'Accept' => '*/*',
                ],
            ]);

            $body = $response->getBody()->getContents();

            return json_decode($body, true);
        } catch (\Throwable $e) {
            throw new BadRequestException('error corpus = '.$e->getMessage());
        }
    }

    public function ask(Project $project, string $query): array
    {
        try {
            $response = $this->client->request('GET', $this->corpusUrl.'/api/v1/ask', [
                'auth' => [$this->corpusUsername, $this->corpusPassword],
                'query' => [
                    'query' => $query,
                    'collection' => $project->getTitle(),
                ],
                'headers' => [
                    'Accept' => '*/*',
                ],
            ]);

            $body = $response->getBody()->getContents();

            return json_decode($body, true);
        } catch (\Throwable $e) {
            throw new BadRequestException('error corpus = '.$e->getMessage());
        }
    }

    public function tasks(): array
    {
        try {
            $response = $this->client->request('GET', $this->corpusUrl.'/api/v1/tasks', [
                'auth' => [$this->corpusUsername, $this->corpusPassword],
                'headers' => [
                    'Accept' => '*/*',
                ],
            ]);

            $body = $response->getBody()->getContents();

            return json_decode($body, true);
        } catch (\Throwable $e) {
            throw new BadRequestException('error corpus = '.$e->getMessage());
        }
    }

    public function task($id): array
    {
        try {
            $response = $this->client->request('GET', $this->corpusUrl.'/api/v1/tasks/'.$id, [
                'auth' => [$this->corpusUsername, $this->corpusPassword],
                'headers' => [
                    'Accept' => '*/*',
                ],
            ]);

            $body = $response->getBody()->getContents();

            return json_decode($body, true);
        } catch (\Throwable $e) {
            throw new BadRequestException('error corpus = '.$e->getMessage());
        }
    }
}
