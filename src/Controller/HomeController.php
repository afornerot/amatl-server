<?php

namespace App\Controller;

use App\Service\GitService;
use App\Service\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private ProjectService $projectService;
    private GitService $gitService;

    public function __construct(ProjectService $projectService, GitService $gitService)
    {
        $this->projectService = $projectService;
        $this->gitService = $gitService;
    }

    #[Route('/', name: 'app_home')]
    public function home(Request $request): Response
    {
        $project = $request->getSession()->get('project');
        if (!$project) {
            return $this->noproject();
        }
        $this->gitService->cloneRepository($project);
        $doc = $request->query->get('doc'); // Récupérer le paramètre doc

        $files = $this->projectService->list($project->getId());
        $readmes = ['/README.html', '/Readme.html', 'readme.html'];
        $readme = false;
        foreach ($readmes as $target) {
            if (($key = array_search($target, $files, true)) !== false) {
                $readme = $key;
                break; // On s'arrête dès qu'on trouve une correspondance
            }
        }

        if (!$doc) {
            $doc = $readme;
        }

        $baseDir = $this->getParameter('kernel.project_dir').'/public/uploads/schemaspy';
        $schemaDir = $baseDir.'/project/'.$project->getId();
        if (!is_dir($schemaDir)) {
            $schemaDir = null;
        } else {
            $schemaDir = '/uploads/schemaspy/'.$project->getId();
        }

        return $this->render('home/home.html.twig', [
            'usemenu' => true,
            'usesidebar' => false,
            'projectId' => $project->getId(),
            'projectUuid' => $project->getUuid(),
            'files' => $files,
            'doc' => $doc,
            'schemaDir' => $schemaDir,
        ]);
    }

    #[Route('/admin', name: 'app_admin')]
    public function admin(): Response
    {
        return $this->render('home/blank.html.twig', [
            'usemenu' => true,
            'usesidebar' => true,
        ]);
    }

    #[Route('/noproject', name: 'app_noproject')]
    public function noproject(): Response
    {
        return $this->render('home/noproject.html.twig', [
            'usemenu' => true,
            'usesidebar' => false,
        ]);
    }
}
