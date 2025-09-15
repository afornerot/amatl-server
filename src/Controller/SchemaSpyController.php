<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SchemaSpyController extends AbstractController
{
    #[Route('/uploads/schemaspy/{id}/{path}', name: 'schemaspy', requirements: ['path' => '.+'])]
    public function serve(int $id, string $path, EntityManagerInterface $em): Response
    {
        $project = $em->getRepository(Project::class)->find($id);
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new \LogicException('L\'utilisateur doit être une instance de User.');
        }

        if (!$project || !$user->getProjects()->contains($project)) {
            throw $this->createAccessDeniedException('Vous ne disposez pas des droits pour visualiser ce document.');
        }

        $file = __DIR__.'/../../public/uploads/schemaspy/'.$id.'/'.$path;

        if (!file_exists($file)) {
            throw $this->createNotFoundException();
        }

        return new Response(file_get_contents($file));
    }
}
