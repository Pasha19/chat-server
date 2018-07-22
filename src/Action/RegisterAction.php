<?php

declare(strict_types=1);

namespace App\Action;

use App\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class RegisterAction implements RequestHandlerInterface
{
    private $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $json = $request->getParsedBody();
        if (!\is_array($json) || !\array_key_exists('name', $json)) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'reason' => 'name not provided',
                ],
                400
            );
        }

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'auth_token' => $this->auth->register($json['name']),
            ],
        ]);
    }
}
