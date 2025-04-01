<?php

namespace App\Controller;

use App\Repository\DocumentRepository;
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
    public function home(Request $request, DocumentRepository $documentRepository): Response
    {
        $project = $request->getSession()->get('project');
        $this->gitService->cloneRepository($project);
        $doc = $request->query->get('doc'); // Récupérer le paramètre doc

        return $this->render('home/home.html.twig', [
            'usemenu' => true,
            'usesidebar' => false,
            'projectId' => $project->getId(),
            'projectUuid' => $project->getUuid(),
            'files' => $this->projectService->list($project->getId()),
            'doc' => $doc,
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

    #[Route('/user/noproject', name: 'app_user_noproject')]
    public function noproject(): Response
    {
        return $this->render('home/noproject.html.twig', [
            'usemenu' => true,
            'usesidebar' => false,
        ]);
    }
}
