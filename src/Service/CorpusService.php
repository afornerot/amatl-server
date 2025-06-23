<?php

namespace App\Service;

use App\Entity\Project;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CorpusService
{
    private Client $client;
    private bool $corpusActivate;
    private string $corpusInstance;
    private string $corpusUrl;
    private string $corpusUsername;
    private string $corpusPassword;
    private LoggerInterface $logger;

    public function __construct(ParameterBagInterface $params, LoggerInterface $logger)
    {
        $this->client = new Client();
        $this->corpusActivate = $params->get('corpusActivate');
        $this->corpusInstance = $params->get('corpusInstance').'-';
        $this->corpusUrl = $params->get('corpusUrl');
        $this->corpusUsername = $params->get('corpusUsername');
        $this->corpusPassword = $params->get('corpusPassword');
        $this->logger = $logger;
    }

    public function indexCorpus(Project $project, string $projectPath, string $filePath): bool
    {
        if (!$this->corpusActivate) {
            return true;
        }

        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Fichier introuvable: $filePath");
        }

        $this->logger->info($this->corpusUrl.'/api/v1/index');

        $sourceUrl = str_replace($projectPath, '', $filePath);
        $response = $this->client->request('POST', $this->corpusUrl.'/api/v1/index', [
            'auth' => [$this->corpusUsername, $this->corpusPassword],
            'multipart' => [
                [
                    'name' => 'collection',
                    'contents' => $this->corpusInstance.$project->getTitle(),
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
        if (!$this->corpusActivate) {
            return [];
        }

        try {
            $response = $this->client->request('GET', $this->corpusUrl.'/api/v1/search', [
                'auth' => [$this->corpusUsername, $this->corpusPassword],
                'query' => [
                    'query' => $query,
                    'collection' => $this->corpusInstance.$project->getTitle(),
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
        if (!$this->corpusActivate) {
            return [];
        }

        try {
            $response = $this->client->request('GET', $this->corpusUrl.'/api/v1/ask', [
                'auth' => [$this->corpusUsername, $this->corpusPassword],
                'query' => [
                    'query' => $query,
                    'collection' => $this->corpusInstance.$project->getTitle(),
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
        if (!$this->corpusActivate) {
            return [];
        }

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
        if (!$this->corpusActivate) {
            return [];
        }

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
