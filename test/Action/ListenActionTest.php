<?php

declare(strict_types=1);

namespace App\Test\Action;

use App\Action\ListenAction;
use App\RequestHandlerSwooleRunner;
use App\Service\UsersConnectionsService;
use App\SwooleEventStreamResponse;
use App\Test\User;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Authentication\UserInterface;

class ListenActionTest extends TestCase
{
    public function testHandle(): void
    {
        $user = new User('name', \md5('uid'));

        $request = new ServerRequest();
        $request = $request
            ->withAttribute(RequestHandlerSwooleRunner::SWOOLE_REQUEST_FD_ATTRIBUTE, 1)
            ->withAttribute(UserInterface::class, $user)
        ;
        /** @var SwooleEventStreamResponse $response */
        $response = Argument::type(SwooleEventStreamResponse::class);
        $usersConnections = $this->prophesize(UsersConnectionsService::class);
        $usersConnections->addUserConnection($user, $response, $request)->shouldBeCalledTimes(1);

        (new ListenAction($usersConnections->reveal()))->handle($request);
    }

    /**
     * @dataProvider dataProvider
     *
     * @expectedException \LogicException
     *
     * @param ServerRequestInterface $serverRequest
     */
    public function testHandleError(ServerRequestInterface $serverRequest): void
    {
        $usersConnections = $this->prophesize(UsersConnectionsService::class);
        /** @var UserInterface $user */
        $user = Argument::any();
        /** @var SwooleEventStreamResponse $response */
        $response = Argument::any();
        /** @var ServerRequestInterface $request */
        $request = Argument::any();
        $usersConnections->addUserConnection($user, $response, $request)->shouldNotBeCalled();

        $action = new ListenAction($usersConnections->reveal());
        $action->handle($serverRequest);
    }

    public function dataProvider(): array
    {
        return [
            'No auth user' => [(new ServerRequest())->withAttribute(RequestHandlerSwooleRunner::SWOOLE_REQUEST_FD_ATTRIBUTE, 1)],
            'No fd' => [new ServerRequest()],
        ];
    }
}
