<?php

declare(strict_types=1);

namespace App\Test\Service;

use App\Exception\BadTokenException;
use App\Service\AuthService;
use App\Service\TokenService;
use App\Test\User;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Authentication\UserInterface;

class AuthServiceTest extends TestCase
{
    public function testAuthenticate(): void
    {
        $token = 'auth_token';
        $uid = \md5('uid');
        $name = 'name';
        $tokenService = $this->prophesize(TokenService::class);
        $tokenService->getUserByToken($token)->willReturn(new User($name, $uid));

        $authService = new AuthService($tokenService->reveal());
        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            'php://memory',
            [
                'Authorization' => $token,
            ]
        );
        /** @var UserInterface $user */
        $user = $authService->authenticate($request);
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertSame($name, $user->getDetail('name'));
        $this->assertSame($uid, $user->getIdentity());
    }

    public function testAuthenticateBadToken(): void
    {
        $token = 'auth_token';
        $tokenService = $this->prophesize(TokenService::class);
        $tokenService->getUserByToken($token)->willThrow(new BadTokenException());
        $authService = new AuthService($tokenService->reveal());
        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            'php://memory',
            [
                'Authorization' => $token,
            ]
        );
        /** @var UserInterface $user */
        $user = $authService->authenticate($request);
        $this->assertNull($user);
    }

    public function testUnauthorizedResponse(): void
    {
        $tokenService = $this->prophesize(TokenService::class);
        $authService = new AuthService($tokenService->reveal());
        $response = $authService->unauthorizedResponse(new ServerRequest());
        $this->assertSame(401, $response->getStatusCode());
        // TODO: test WWW-Authenticate header
    }
}
