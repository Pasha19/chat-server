<?php

declare(strict_types=1);

namespace App\Action;

use App\Service\AuthService;
use App\Service\UsernameValidatorService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class RegisterAction implements RequestHandlerInterface
{
    private $auth;
    private $usernameValidator;

    public function __construct(AuthService $auth, UsernameValidatorService $usernameValidator)
    {
        $this->auth = $auth;
        $this->usernameValidator = $usernameValidator;
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
        $name = $json['name'];

        if (!($this->usernameValidator)($name)) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'reason' => 'name not valid',
                ],
                400
            );
        }

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'auth_token' => $this->auth->register($name),
            ],
        ]);
    }
}
