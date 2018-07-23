<?php

declare(strict_types=1);

namespace App\Test\Service;

use App\Data\User;
use App\Exception\UserConnectionExistsException;
use App\Exception\UserConnectionNotExistsException;
use App\Http\SwooleResponseHandler;
use App\Http\SwooleServerRequest;
use App\Service\UsersConnectionsService;
use Lcobucci\JWT\Builder;
use PHPUnit\Framework\TestCase;
use Zend\Expressive\Authentication\UserInterface;

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
        $user = $this->createUser(\md5('5'), 'User5');
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
        $user = $this->createUser(\md5((string) $id), 'User'.$id);
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
            function (SwooleResponseHandler $response) use (&$i): void {
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
            function (SwooleResponseHandler $response) use (&$i): void {
                $this->assertSame(++$i, $response->getStatusCode());
            },
            \md5('4')
        );
        $this->assertSame(3, $i);

        return $usersConnections;
    }

    private function addUserConnection(UsersConnectionsService $usersConnections, int $id): UsersConnectionsService
    {
        $user = $this->createUser(\md5((string) $id), 'User'.$id);
        $response = $this->prophesize(SwooleResponseHandler::class);
        $response->getStatusCode()->willReturn($id);
        $request = $this->prophesize(SwooleServerRequest::class);
        $request->getFd()->willReturn($id);
        $usersConnections->addUserConnection($user, $response->reveal(), $request->reveal());

        return $usersConnections;
    }

    private function createUser(string $uid, string $name): UserInterface
    {
        $token = (new Builder())
//            ->setIssuedAt($time)
//            ->setNotBefore($time)
//            ->setExpiration($time + self::SECONDS_IN_MONTH)
            ->set('name', $name)
            ->set('uid', $uid)
            ->getToken()
        ;

        return new User($token);
    }
}
