<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\ProjectRepository;
use Bnine\FilesBundle\Security\AbstractFileVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FileVoter extends AbstractFileVoter
{
    private ProjectRepository $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    protected function canView(string $domain, $id, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return true;
    }

    protected function canEdit(string $domain, $id, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }
        if ($user->hasRole('ROLE_ADMIN')) {
            return true;
        }

        switch ($domain) {
            case 'project':
                $project = $this->projectRepository->find($id);
                if ($project && $project->getUsers()->contains($user)) {
                    return true;
                }
                break;
        }

        return false;
    }

    protected function canDelete(string $domain, $id, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }
        if ($user->hasRole('ROLE_ADMIN')) {
            return true;
        }

        switch ($domain) {
            case 'project':
                $project = $this->projectRepository->find($id);
                if ($project && $project->getUsers()->contains($user)) {
                    return true;
                }
                break;
        }

        return false;
    }
}