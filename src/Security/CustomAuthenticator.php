<?php

namespace App\Security;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomAuthenticator extends JWTAuthenticator
{
    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $eventDispatcher,
        TokenExtractorInterface $tokenExtractor,
        UserProviderInterface $userProvider,
        TranslatorInterface $translator = null,
    ) {
        parent::__construct($jwtManager, $eventDispatcher, $tokenExtractor, $userProvider, $translator);
    }

    public function doAuthenticate(Request $request): Passport|SelfValidatingPassport
   {
       $token = $this->getTokenExtractor()->extract($request);

       $payload = $this->getJwtManager()->parse($token);

       if (!in_array('ROLE_ADMIN', $payload['roles'])) {
           throw new AccessDeniedException();
       }

       return parent::doAuthenticate($request);
   }
}