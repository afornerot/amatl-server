<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

final class SessionListener
{
    private Security $security;
    private RouterInterface $router;

    public function __construct(Security $security, RouterInterface $router)
    {
        $this->security = $security;
        $this->router = $router;
    }

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        $user = $this->security->getUser();
        if ($user instanceof User) {
            // Intialisation de la compagnie en cours
            if (!$user->getProject()) {
                if ($user->getProjects()) {
                    $user->setProject($user->getProjects()[0]);
                }
            }

            $session->set('project', $user->getProject());
            $session->set('projects', $user->getProjects());

            $currentPath = $request->getPathInfo();
            $noProjectPath = $this->router->generate('app_user_noproject');
            if (!$user->getProject() && 0 === stripos($currentPath, 'noproject')) {
                $event->setResponse(new RedirectResponse($noProjectPath));
            }
        }
    }
}
