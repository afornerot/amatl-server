<?php

namespace App\Controller;

use App\Service\CorpusService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CorpusController extends AbstractController
{
    private CorpusService $corpusService;

    public function __construct(CorpusService $corpusService)
    {
        $this->corpusService = $corpusService;
    }

    #[Route('/corpus', name: 'app_corpus')]
    public function home(Request $request): Response
    {
        $project = $request->getSession()->get('project');
        if (!$project) {
            return $this->redirectToRoute('app_noproject');
        }

        return $this->render('corpus/ask.html.twig', [
            'usemenu' => true,
            'usesidebar' => false,
            'maxwidth' => 1000,
            'project' => $project,
        ]);
    }

    #[Route('/corpus/ask', name: 'app_corpus_ask', methods: ['POST'])]
    public function ask(Request $request): JsonResponse
    {
        $project = $request->getSession()->get('project');
        $data = json_decode($request->getContent(), true);

        if (!isset($data['question']) || empty($data['question'])) {
            return new JsonResponse(['error' => 'Aucune question fournie.'], 400);
        }

        $question = $data['question'];

        try {
            $answer = $this->corpusService->ask($project, $question);
            $search = $this->corpusService->search($project, $question);

            $response = [
                'awnser' => $answer,
                'search' => $search,
            ];

            return new JsonResponse(['response' => $response]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la recherche: '.$e->getMessage()], 500);
        }
    }

    #[Route('/admin/corpus', name: 'app_corpus_task')]
    public function task(Request $request): Response
    {
        $tasks = $this->corpusService->tasks();
        foreach ($tasks['tasks'] as $key => $task) {
            $tasks['tasks'][$key] = $this->corpusService->task($task['id'])['task'];
        }
        dump($tasks);

        return $this->render('corpus/task.html.twig', [
            'usemenu' => true,
            'usesidebar' => true,
            'tasks' => $tasks,
        ]);
    }
}
