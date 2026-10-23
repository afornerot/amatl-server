<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class DynamicAuthenticator extends AbstractAuthenticator
{
    public function __construct(private readonly string $modeAuth, private readonly UserRepository $userRepository, private readonly CasUserProvider $casUserProvider, private readonly ParameterBagInterface $parameterBag)
    {
    }

    public function supports(Request $request): ?bool
    {
        // Vérifie si l'utilisateur est déjà connecté
        if ($request->getSession()->get('_security_main')) {
            return false; // L'utilisateur est déjà authentifié
        }

        // Exclure les routes de login et logout pour éviter les boucles
        $currentPath = $request->getPathInfo();
        if (in_array($currentPath, ['/login', '/logout'])) {
            return false;
        }

        return true;
    }

    public function authenticate(Request $request): Passport
    {
        return match ($this->modeAuth) {
            'SQL' => $this->authenticateWithSql($request),
            'CAS' => $this->authenticateWithCas($request),
            default => throw new \InvalidArgumentException('Invalid authentication method'),
        };
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw $exception;
    }

    private function authenticateWithSql(Request $request): Passport
    {
        $username = $request->request->get('_username', '');
        $password = $request->request->get('_password', '');

        if (!$username || !$password) {
            throw new AuthenticationException('Username and password are required.');
        }

        // Charger l'utilisateur via Doctrine
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            throw new AuthenticationException('User not found.');
        }

        return new Passport(
            new UserBadge($username, fn($userIdentifier) => $this->userRepository->findOneBy(['username' => $userIdentifier])),
            new PasswordCredentials($password)
        );
    }

    private function authenticateWithCas(Request $request): Passport
    {
        $forwardedHost = $request->headers->get('X-Forwarded-Host');
        $forwardedPort = $request->headers->get('X-Forwarded-Port');
        $forwardedProto = $request->headers->get('X-Forwarded-Proto');

        $host = $forwardedHost ?? $request->getHost();
        $port = $forwardedPort ?? $request->getPort();
        $scheme = $forwardedProto ?? $request->getScheme();

        $url = $scheme.'://'.$host.':'.$port;

        // \phpCAS::setDebug('/tmp/logcas.log');
        \phpCAS::client(CAS_VERSION_2_0, $this->parameterBag->get('casHost'), (int) $this->parameterBag->get('casPort'), $this->parameterBag->get('casPath'), $url, false);
        \phpCAS::setNoCasServerValidation();
        \phpCAS::forceAuthentication();

        $username = \phpCAS::getUser();
        $attributes = \phpCAS::getAttributes();

        if (!$username) {
            throw new AuthenticationException('CAS authentication failed.');
        }

        $userBadge = new UserBadge($username, fn($userIdentifier) => $this->casUserProvider->loadUserByIdentifierAndAttributes($userIdentifier, $attributes));

        return new SelfValidatingPassport($userBadge);
    }
}
