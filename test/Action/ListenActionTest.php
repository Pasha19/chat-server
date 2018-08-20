<?php

declare(strict_types=1);

namespace App\Test\Action;

use App\Action\ListenAction;
use App\RequestHandlerSwooleRunner;
use App\Service\EventStreamFormatterService;
use App\Service\MessageStorageService;
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
        $lastEventId = \md5((string) \time());
        $request = $request
            ->withAttribute(RequestHandlerSwooleRunner::SWOOLE_REQUEST_FD_ATTRIBUTE, 1)
            ->withAttribute(UserInterface::class, $user)
            ->withQueryParams([ListenAction::LAST_EVENT_ID_PARAM => $lastEventId])
        ;

        /** @var SwooleEventStreamResponse $response */
        $response = Argument::type(SwooleEventStreamResponse::class);
        $usersConnections = $this->prophesize(UsersConnectionsService::class);
        $usersConnections->addUserConnection($user, $response, $request)->shouldBeCalledOnce();

        $messageStorage = $this->prophesize(MessageStorageService::class);
        $id = \md5('last_id');
        $messages = [
            [
                'message' => 'message1',
                'id' => \md5('id'),
            ],
            [
                'message' => 'message2',
                'id' => $id,
            ],
        ];
        $messageStorage
            ->getMessagesLaterThan($lastEventId)
            ->willReturn($messages)
        ;

        $eventStreamFormatter = $this->prophesize(EventStreamFormatterService::class);
        $data = 'data: '.\json_encode($messages)."\n\n";
        $eventStreamFormatter
            ->getEventStreamMessage([
                'event' => 'connect',
                'data' => \json_encode([
                    'status' => 'success',
                    'data' => $messages,
                ]),
                'id' => $id,
            ])
            ->willReturn($data)
            ->shouldBeCalledOnce()
        ;

        (new ListenAction($usersConnections->reveal(), $messageStorage->reveal(), $eventStreamFormatter->reveal()))->handle($request);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param ServerRequestInterface $serverRequest
     */
    public function testHandleError(ServerRequestInterface $serverRequest): void
    {
        $this->expectException(\LogicException::class);

        $usersConnections = $this->prophesize(UsersConnectionsService::class);
        /** @var UserInterface $user */
        $user = Argument::any();
        /** @var SwooleEventStreamResponse $response */
        $response = Argument::any();
        /** @var ServerRequestInterface $request */
        $request = Argument::any();
        $usersConnections->addUserConnection($user, $response, $request)->shouldNotBeCalled();

        $messageStorage = $this->prophesize(MessageStorageService::class);
        /** @var string $string */
        $string = Argument::type('string');
        $messageStorage->getMessagesLaterThan($string)->shouldNotBeCalled();

        $eventStreamFormatter = $this->prophesize(EventStreamFormatterService::class);

        (new ListenAction($usersConnections->reveal(), $messageStorage->reveal(), $eventStreamFormatter->reveal()))->handle($serverRequest);
    }

    public function dataProvider(): array
    {
        return [
            'No auth user' => [(new ServerRequest())->withAttribute(RequestHandlerSwooleRunner::SWOOLE_REQUEST_FD_ATTRIBUTE, 1)],
            'No fd' => [new ServerRequest()],
        ];
    }
}
