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

        return $this->render('home/corpus.html.twig', [
            'usemenu' => true,
            'usesidebar' => false,
            'maxwidth' => 1000,
            'project' => $project,
        ]);
    }

    #[Route('/corpus/search', name: 'app_corpus_search', methods: ['POST'])]
    public function search(Request $request): JsonResponse
    {
        $project = $request->getSession()->get('project');

        $data = json_decode($request->getContent(), true);
        dump($data);

        if (!isset($data['question']) || empty($data['question'])) {
            return new JsonResponse(['error' => 'Aucune question fournie.'], 400);
        }

        $question = $data['question'];

        try {
            $answer = $this->corpusService->search($project, $question);

            return new JsonResponse(['response' => $answer]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la recherche: '.$e->getMessage()], 500);
        }
    }
}
