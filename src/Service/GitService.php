<?php

namespace App\Service;

use App\Entity\Project;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GitService
{
    private string $uploadBasePath;
    private LoggerInterface $logger;

    public function __construct(ParameterBagInterface $params, LoggerInterface $logger)
    {
        $this->uploadBasePath = $params->get('kernel.project_dir').'/uploads/';
        $this->logger = $logger;
    }


    public function cloneRepository(Project $project): bool
    {
        $projectPath = $this->getProjectPath($project->getId());

        // Vérifie que le projet est lié à un repository
        if(!$project->getGitUrl()) {
            return true;
        }

        // Vérifie si le projet existe déjà
        if (is_dir($projectPath . '/.git')) {
            $this->logger->info("Le dépôt existe déjà pour le projet {$project->getId()}, mise à jour avec git pull...");
            return $this->updateRepository($project);
        }

        // Si un token est fourni, on l’intègre dans l'URL
        $repoUrl=$project->getGitUrl();
        if ($project->getGitUsername() && $project->getGitToken()) {
            $repoUrl = preg_replace('#^(https?://)#', '$1' . urlencode($project->getGitUsername()) . ':' . urlencode($project->getGitToken()) . '@', $project->getGitUrl());
        }

        // Exécute la commande git clone
        $process = new Process(['git', 'clone', $repoUrl, $projectPath]);
        $process->setTimeout(null); // 🔥 Désactivation du timeout 🔥
        $process->start(); // Lancer le process en asynchrone
    
        while ($process->isRunning()) {
            // Optionnel : Afficher la progression (utile pour debug)
            $this->logger->info("⏳ Clonage en cours...");
            sleep(5); // Évite de spammer les logs toutes les millisecondes
        }        

        if (!$process->isSuccessful()) {
            $this->logger->error("Échec du clonage du dépôt : " . $process->getErrorOutput());
            throw new ProcessFailedException($process);
        }

        $this->logger->info("Dépôt cloné avec succès dans {$projectPath}");
        return true;
    }

    public function updateRepository(Project $project): bool
    {
        $projectPath = $this->getProjectPath($project->getId());

        if (!is_dir($projectPath . '/.git')) {
            $this->logger->error("Le projet {$project->getId()} n'est pas un dépôt Git !");
            throw new \Exception("Le projet {$project->getId()} n'est pas un dépôt Git.");
        }

        // Exécute git pull
        $process = new Process(['git', '-C', $projectPath, 'pull']);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->logger->error("Erreur lors de la mise à jour du dépôt : " . $process->getErrorOutput());
            throw new ProcessFailedException($process);
        }

        $this->logger->info("Dépôt mis à jour avec succès pour le projet {$project->getId()}");
        return true;
    }

    private function getProjectPath(string $projectId): string
    {
        $projectPath = $this->uploadBasePath . $projectId;

        return $projectPath;
    }
}
