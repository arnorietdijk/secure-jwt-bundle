<?php

declare(strict_types=1);

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWT\Security\Guard;

use ConnectHolland\SecureJWT\Entity\InvalidToken;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator as BaseAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class JWTTokenAuthenticator extends BaseAuthenticator
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine, JWTTokenManagerInterface $jwtManager, EventDispatcherInterface $dispatcher, TokenExtractorInterface $tokenExtractor)
    {
        parent::__construct($jwtManager, $dispatcher, $tokenExtractor);

        $this->doctrine = $doctrine;
    }

    /**
     * Add token validation to guard.
     */
    public function getCredentials(Request $request)
    {
        $token      = $this->getTokenExtractor()->extract($request);
        $repository = $this->doctrine->getRepository(InvalidToken::class);

        if (!$repository instanceof EntityRepository) {
            throw new \RuntimeException('Unable to verify token because doctrine is not set up correctly. Please configure `vendor/connectholland/secure-jwt/src/Entity` as an annotated entity path (see README.md for more details)');
        }

        if ($repository->findOneBy(['token' => $token]) instanceof InvalidToken) {
            throw new InvalidTokenException('Invalidated JWT Token');
        }

        return parent::getCredentials($request);
    }
}