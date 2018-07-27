<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\UserInterface;

class AuthService implements AuthenticationInterface
{
    private $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        if (!$request->hasHeader('Authorization')) {
            return null;
        }
        $authorization = $request->getHeader('Authorization')[0];
        if (\mb_strpos($authorization, 'Bearer ') !== 0) {
            return null;
        }
        $authorization = \mb_substr($authorization, \mb_strlen('Bearer '));

        try {
            return $this->tokenService->getUserByToken($authorization);
        } catch (\Throwable $e) {
            // TODO: log or path auth error
            return null;
        }
    }

    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: add WWW-Authenticate header to response
        return new JsonResponse(
            [
                'status' => 'error',
                'reason' => 'authentication required',
            ],
            401
        );
    }
}
