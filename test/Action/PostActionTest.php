<?php

declare(strict_types=1);

namespace App\Test\Action;

use App\Action\PostAction;
use App\Service\EventStreamFormatterService;
use App\Service\MessageStorageService;
use App\Service\UsersConnectionsService;
use App\SwooleEventStreamResponse;
use App\Test\StringStream;
use App\Test\User;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Authentication\UserInterface;

class PostActionTest extends TestCase
{
    use AssertResponseFormat;

    public function testHandle(): void
    {
        $uid = \md5('uid');
        $user = new User('name', $uid);
        $requestJson = [
            'message' => 'New message',
        ];
        $request = new ServerRequest(
            [],
            [],
            '/api/chat/post',
            'POST',
            StringStream::create((string) \json_encode($requestJson)),
            ['Content-Type' => 'application/json'],
            [],
            [],
            $requestJson
        );
        $request = $request->withAttribute(UserInterface::class, $user);
        $self = $this;

        $eventStreamFormatter = $this->prophesize(EventStreamFormatterService::class);
        /** @var array $array */
        $array = Argument::type('array');
        $eventStreamMessage = '';
        $eventStreamFormatter
            ->getEventStreamMessage($array)
            ->will(function (array $args) use ($self, $requestJson, $user, &$eventStreamMessage): string {
                $message = $args[0];

                $self::assertArrayHasKey('event', $message);
                $self::assertSame('post', $message['event']);

                $self::assertArrayHasKey('data', $message);
                $self::assertJson($message['data']);
                $data = \json_decode($message['data'], true);
                $self::assertArrayHasKey('status', $data);
                $self::assertSame('success', $data['status']);

                $self::assertArrayHasKey('data', $data);
                $data = $data['data'];
                $self::assertArrayHasKey('message', $data);
                $self::assertSame($requestJson['message'], $data['message']);
                $self::assertArrayHasKey('time', $data);
                $self::assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\+\d{4}$/', $data['time']);

                $self::assertArrayHasKey('user', $data);
                $userData = $data['user'];
                $self::assertArrayHasKey('name', $userData);
                $self::assertSame($user->getDetail('name'), $userData['name']);
                $self::assertArrayHasKey('uid', $userData);
                $self::assertSame($user->getIdentity(), $userData['uid']);

                $self::assertArrayHasKey('id', $message);
                $self::assertInternalType('string', $message['id']);

                $result = "event: {$message['event']}\ndata: ".\json_encode($message['data'])."\n\n";
                $eventStreamMessage = $result;

                return $result;
            })
            ->shouldBeCalledOnce()
        ;

        $stream = $this->prophesize(StreamInterface::class);
        /** @var string $string */
        $string = Argument::type('string');
        $stream
            ->write($string)
            ->will(function (array $args) use ($self, &$eventStreamMessage): void {
                $self::assertSame($eventStreamMessage, $args[0]);
            })
            ->shouldBeCalledOnce()
        ;

        $swooleEventStreamResponse = $this->prophesize(SwooleEventStreamResponse::class);
        $swooleEventStreamResponse->getBody()->willReturn($stream->reveal());

        $usersConnections = $this->prophesize(UsersConnectionsService::class);
        /** @var \Closure $closure */
        $closure = Argument::type(\Closure::class);
        $usersConnections
            ->walk($closure, $uid)
            ->will(
                function (array $args) use ($self, $uid, $swooleEventStreamResponse): void {
                    $self::assertSame($uid, $args[1]);
                    $args[0]($swooleEventStreamResponse->reveal());
                }
            )
            ->shouldBeCalledOnce()
        ;

        $messageStorage = $this->prophesize(MessageStorageService::class);
        /** @var array $array */
        $array = Argument::type('array');
        $messageStorage
            ->add($array)
            ->will(
                function (array $args) use ($self, $user, $requestJson): void {
                    $message = $args[0];

                    $self::assertArrayHasKey('user', $message);
                    $userData = $message['user'];
                    $self::assertArrayHasKey('name', $userData);
                    $self::assertSame($user->getDetail('name'), $userData['name']);
                    $self::assertArrayHasKey('uid', $userData);
                    $self::assertSame($user->getIdentity(), $userData['uid']);

                    $self::assertArrayHasKey('message', $message);
                    $self::assertSame($requestJson['message'], $message['message']);

                    $self::assertArrayHasKey('time', $message);
                    $self::assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\+\d{4}$/', $message['time']);

                    $self::assertArrayHasKey('id', $message);
                    $self::assertInternalType('string', $message['id']);
                }
            )
            ->shouldBeCalledOnce()
        ;

        $action = new PostAction($usersConnections->reveal(), $messageStorage->reveal(), $eventStreamFormatter->reveal());
        $response = $action->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $responseBody = $response->getBody()->getContents();
        $data = $this->assertSuccessResponseFormat($responseBody);
        $this->assertArrayHasKey('time', $data);
        $this->assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\+\d{4}$/', $data['time']);
        $this->assertArrayHasKey('id', $data);
        $this->assertInternalType('string', $data['id']);
    }
}
