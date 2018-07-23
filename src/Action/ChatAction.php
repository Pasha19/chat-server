<?php

declare(strict_types=1);

namespace App\Action;

use App\Service\UsersConnectionsService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Authentication\UserInterface;

abstract class ChatAction implements RequestHandlerInterface
{
    private $usersConnection;

    public function __construct(UsersConnectionsService $usersConnections)
    {
        $this->usersConnection = $usersConnections;
    }

    protected function getUser(ServerRequestInterface $request): UserInterface
    {
        $user = $request->getAttribute(UserInterface::class, null);
        if (!$user instanceof UserInterface) {
            throw new \LogicException('User not provided. Probably authentication not configured.');
        }

        return $user;
    }

    protected function getUsersConnections(): UsersConnectionsService
    {
        return $this->usersConnection;
    }
}
