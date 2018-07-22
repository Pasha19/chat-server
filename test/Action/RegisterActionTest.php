<?php

declare(strict_types=1);

namespace App\Test\Action;

use App\Action\RegisterAction;
use App\Service\AuthService;
use App\Service\UsernameValidatorService;
use App\Test\StringStream;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\Diactoros\ServerRequest;

class RegisterActionTest extends TestCase
{
    use AssertResponseFormat;

    public function testRegister(): void
    {
        $name = 'Tester';
        $token = 'jwt_token';
        $auth = $this->prophesize(AuthService::class);
        $auth->register($name)->shouldBeCalledTimes(1)->willReturn($token);
        $action = new RegisterAction($auth->reveal(), new UsernameValidatorService());
        $requestJson = ['name' => $name];
        $serverRequest = new ServerRequest(
            [],
            [],
            '/register',
            'POST',
             StringStream::create((string) \json_encode($requestJson)),
            ['Content-Type' => 'application/json'],
            [],
            [],
            $requestJson
        );

        $response = $action->handle($serverRequest);
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = $response->getBody()->getContents();
        $data = $this->assertSuccessResponseFormat($responseBody);
        $this->assertArrayHasKey('auth_token', $data);
        $this->assertSame($token, $data['auth_token']);
    }

    public function testBadRegister(): void
    {
        $auth = $this->prophesize(AuthService::class);
        /** @var string $any */
        $any = Argument::any();
        $auth->register($any)->shouldNotBeCalled();

        $action = new RegisterAction($auth->reveal(), new UsernameValidatorService());
        $requestJson = ['foo' => 'bar'];
        $serverRequest = new ServerRequest(
            [],
            [],
            '/register',
            'POST',
            StringStream::create((string) \json_encode($requestJson)),
            ['Content-Type' => 'application/json'],
            [],
            [],
            $requestJson
        );

        $response = $action->handle($serverRequest);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = $response->getBody()->getContents();
        $this->assertErrorResponseFormat($responseBody, 'name not provided');
    }

    public function testBadName(): void
    {
        /** @var ObjectProphecy&AuthService */
        $auth = $this->prophesize(AuthService::class);
        /** @var string $any */
        $any = Argument::any();
        $auth->register($any)->shouldNotBeCalled();

        $action = new RegisterAction($auth->reveal(), new UsernameValidatorService());
        $requestJson = ['name' => 'E:123'];
        $serverRequest = new ServerRequest(
            [],
            [],
            '/register',
            'POST',
            StringStream::create((string) \json_encode($requestJson)),
            ['Content-Type' => 'application/json'],
            [],
            [],
            $requestJson
        );

        $response = $action->handle($serverRequest);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = $response->getBody()->getContents();
        $this->assertErrorResponseFormat($responseBody, 'name not valid');
    }
}
