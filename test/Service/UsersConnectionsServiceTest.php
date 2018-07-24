<?php

declare(strict_types=1);

namespace App\Test\Service;

use App\Exception\UserConnectionExistsException;
use App\Exception\UserConnectionNotExistsException;
use App\RequestHandlerSwooleRunner;
use App\Service\UsersConnectionsService;
use App\SwooleEventStreamResponse;
use App\Test\User;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\ServerRequest;

class UsersConnectionsServiceTest extends TestCase
{
    public function testAddNewConnection(): UsersConnectionsService
    {
        $usersConnections = new UsersConnectionsService();
        for ($i = 1; $i <= 5; ++$i) {
            $this->addUserConnection($usersConnections, $i);
        }

        $this->assertTrue(true, 'test no exception');

        return $usersConnections;
    }

    /**
     * @depends testAddNewConnection
     *
     * @param UsersConnectionsService $usersConnections
     *
     * @return UsersConnectionsService
     */
    public function testAddExistingUser(UsersConnectionsService $usersConnections): UsersConnectionsService
    {
        $id = 1;
        $exception = false;
        try {
            $this->addUserConnection($usersConnections, $id);
        } catch (UserConnectionExistsException $e) {
            $this->assertSame(\sprintf('Connection for uid "%s" already exists. fd: %d', \md5((string) $id), $id), $e->getMessage());
            $exception = true;
        }
        $this->assertTrue($exception);

        return $usersConnections;
    }

    /**
     * @depends testAddExistingUser
     *
     * @param UsersConnectionsService $usersConnections
     *
     * @return UsersConnectionsService
     */
    public function testDeleteExistingUserByUser(UsersConnectionsService $usersConnections): UsersConnectionsService
    {
        $this->assertTrue(true, 'test no exception');
        $user = new User('User5', \md5('5'));
        $usersConnections->removeConnectionByUser($user);

        return $usersConnections;
    }

    /**
     * @depends testDeleteExistingUserByUser
     *
     * @param UsersConnectionsService $usersConnections
     *
     * @return UsersConnectionsService
     */
    public function testDeleteNotExistingUserByUser(UsersConnectionsService $usersConnections): UsersConnectionsService
    {
        $id = 5;
        $exception = false;
        $user = new User('User'.$id, \md5((string) $id));
        try {
            $usersConnections->removeConnectionByUser($user);
        } catch (UserConnectionNotExistsException $e) {
            $this->assertSame(\sprintf('Connection for uid "%s" not found', \md5((string) $id)), $e->getMessage());
            $exception = true;
        }
        $this->assertTrue($exception);

        return $usersConnections;
    }

    /**
     * @depends testDeleteNotExistingUserByUser
     *
     * @param UsersConnectionsService $usersConnections
     *
     * @return UsersConnectionsService
     */
    public function testDeleteExistingUserByFd(UsersConnectionsService $usersConnections): UsersConnectionsService
    {
        $usersConnections->removeConnectionByFd(1);

        $this->assertTrue(true, 'test no exception');

        return $usersConnections;
    }

    /**
     * @depends testDeleteExistingUserByFd
     *
     * @param UsersConnectionsService $usersConnections
     *
     * @return UsersConnectionsService
     */
    public function testDeleteNotExistingUserByFd(UsersConnectionsService $usersConnections): UsersConnectionsService
    {
        $usersConnections->removeConnectionByFd(1);

        $this->assertTrue(true, 'test no exception');

        return $usersConnections;
    }

    /**
     * @depends testDeleteNotExistingUserByFd
     *
     * @param UsersConnectionsService $usersConnections
     *
     * @return UsersConnectionsService
     */
    public function testWalkWithNoExclude(UsersConnectionsService $usersConnections): UsersConnectionsService
    {
        $i = 1;
        $usersConnections->walk(
            function (SwooleEventStreamResponse $response) use (&$i): void {
                $this->assertSame(++$i, $response->getStatusCode());
            }
        );
        $this->assertSame(4, $i);

        return $usersConnections;
    }

    /**
     * @depends testWalkWithNoExclude
     *
     * @param UsersConnectionsService $usersConnections
     *
     * @return UsersConnectionsService
     */
    public function testWalkWithExclude(UsersConnectionsService $usersConnections): UsersConnectionsService
    {
        $i = 1;
        $usersConnections->walk(
            function (SwooleEventStreamResponse $response) use (&$i): void {
                $this->assertSame(++$i, $response->getStatusCode());
            },
            \md5('4')
        );
        $this->assertSame(3, $i);

        return $usersConnections;
    }

    private function addUserConnection(UsersConnectionsService $usersConnections, int $id): UsersConnectionsService
    {
        $user = new User('User'.$id, \md5((string) $id));
        $stream = $this->prophesize(StreamInterface::class);
        $response = $this->prophesize(SwooleEventStreamResponse::class);
        $response->getStatusCode()->willReturn($id);
        $response->getBody()->willReturn($stream->reveal());
        $request = new ServerRequest();
        $request = $request->withAttribute(RequestHandlerSwooleRunner::SWOOLE_REQUEST_FD_ATTRIBUTE, $id);
        $usersConnections->addUserConnection($user, $response->reveal(), $request);

        return $usersConnections;
    }
}
