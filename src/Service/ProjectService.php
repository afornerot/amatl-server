<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProjectService
{
    private string $uploadBasePath;

    public function __construct(ParameterBagInterface $params)
    {
        $this->uploadBasePath = $params->get('kernel.project_dir').'/uploads/';
    }

    public function getProjectDir(int $projectId): string
    {
        return $this->uploadBasePath.$projectId.'/';
    }

    public function list(int $projectId): array
    {
        $projectDir = $this->getProjectDir($projectId);
        if (!file_exists($projectDir)) {
            return [];
        }

        return $this->scan($projectDir, $projectDir);
    }

    private function scan(string $dir, string $baseDir): array
    {
        $files = [];
        foreach (scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) {
                continue;
            }

            $fullPath = $dir.DIRECTORY_SEPARATOR.$file;
            $relativePath = str_replace($baseDir, '', $fullPath);

            if (is_dir($fullPath)) {
                $subFiles = $this->scan($fullPath, $baseDir);
                if (!empty($subFiles)) {
                    $files[$file] = $subFiles; // Stocker le sous-dossier sous son vrai nom
                }
            } elseif ('html' === pathinfo($file, PATHINFO_EXTENSION)) {
                $files[$file] = $relativePath; // Associer le fichier à son vrai nom
            }
        }

        return $files;
    }
}
