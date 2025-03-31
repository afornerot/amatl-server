<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProjectController extends AbstractController
{
    #[Route('/admin/project', name: 'app_admin_project')]
    public function list(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findAll();

        return $this->render('project/list.html.twig', [
            'usemenu' => true,
            'usesidebar' => true,
            'title' => 'Liste des Projets',
            'routesubmit' => 'app_admin_project_submit',
            'routeupdate' => 'app_admin_project_update',
            'projects' => $projects,
        ]);
    }

    #[Route('/admin/project/submit', name: 'app_admin_project_submit')]
    public function submit(Request $request, EntityManagerInterface $em): Response
    {
        $project = new Project();
        $project->setUuid(Uuid::uuid4());

        $form = $this->createForm(ProjectType::class, $project, ['mode' => 'submit']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newToken = $form->get('gitToken')->getData();
            if ($newToken) {
                $project->setGitToken($newToken);
            }

            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('app_admin_project');
        }

        return $this->render('project/edit.html.twig', [
            'usemenu' => true,
            'usesidebar' => true,
            'title' => 'Création Projet',
            'routecancel' => 'app_admin_project',
            'routedelete' => 'app_admin_project_delete',
            'mode' => 'submit',
            'form' => $form,
        ]);
    }

    #[Route('/admin/project/update/{id}', name: 'app_admin_project_update')]
    public function update(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $project = $em->getRepository(Project::class)->find($id);
        if (!$project) {
            return $this->redirectToRoute('app_admin_project');
        }

        $form = $this->createForm(ProjectType::class, $project, ['mode' => 'update']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newToken = $form->get('gitToken')->getData();
            if ($newToken) {
                $project->setGitToken($newToken);
            }

            $em->flush();

            return $this->redirectToRoute('app_admin_project');
        }

        return $this->render('project/edit.html.twig', [
            'usemenu' => true,
            'usesidebar' => true,
            'title' => 'Modification Projet = '.$project->getTitle(),
            'routecancel' => 'app_admin_project',
            'routedelete' => 'app_admin_project_delete',
            'mode' => 'update',
            'form' => $form,
        ]);
    }

    #[Route('/admin/project/delete/{id}', name: 'app_admin_project_delete')]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        // Récupération de l'enregistrement courant
        $project = $em->getRepository(Project::class)->find($id);
        if (!$project) {
            return $this->redirectToRoute('app_admin_project');
        }

        // Tentative de suppression
        try {
            $em->remove($project);
            $em->flush();
        } catch (\Exception $e) {
            $this->addflash('error', $e->getMessage());

            return $this->redirectToRoute('app_admin_project_update', ['id' => $id]);
        }

        return $this->redirectToRoute('app_admin_project');
    }

    #[Route('/user/view/{idProject}/{filePath}', name: 'view_file', requirements: ['filePath' => '.+'])]
    public function viewFile(int $idProject, string $filePath, EntityManagerInterface $em): Response
    {
        $project = $em->getRepository(Project::class)->find($idProject);
        if (!$project || !$this->getUser()->getProjects()->contains($project)) {
            throw $this->createAccessDeniedException('Vous ne disposez pas des droits pour visualiser ce document.');
        }

        $basePath = $this->getParameter('kernel.project_dir')."/uploads/{$idProject}/";
        $fullPath = realpath($basePath.$filePath);

        // Vérification de sécurité : s'assurer que le fichier est bien dans le dossier du projet
        if (!$fullPath || !str_starts_with($fullPath, $basePath) || !file_exists($fullPath)) {
            throw $this->createNotFoundException("Le fichier demandé n'existe pas.");
        }

        $htmlContent = file_get_contents($fullPath);

        return new Response($htmlContent);
    }
}
