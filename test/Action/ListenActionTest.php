<?php

declare(strict_types=1);

namespace App\Test\Action;

use App\Action\ListenAction;
use App\Http\SwooleResponseHandler;
use App\Http\SwooleServerRequest;
use App\Service\UsersConnectionsService;
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

        $request = new SwooleServerRequest();
        $request = $request->withAttribute(UserInterface::class, $user);
        /** @var SwooleResponseHandler $response */
        $response = Argument::type(SwooleResponseHandler::class);
        $usersConnections = $this->prophesize(UsersConnectionsService::class);
        $usersConnections->addUserConnection($user, $response, $request)->shouldBeCalledTimes(1);

        $action = new ListenAction($usersConnections->reveal());
        $this->assertInstanceOf(SwooleResponseHandler::class, $action->handle($request));
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
        /** @var SwooleResponseHandler $response */
        $response = Argument::any();
        /** @var SwooleServerRequest $request */
        $request = Argument::any();
        $usersConnections->addUserConnection($user, $response, $request)->shouldNotBeCalled();

        $action = new ListenAction($usersConnections->reveal());
        $action->handle($serverRequest);
    }

    public function dataProvider(): array
    {
        return [
            'No auth user' => [new SwooleServerRequest()],
            'Wrong type' => [new ServerRequest()],
        ];
    }
}
