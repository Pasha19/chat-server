<?php

declare(strict_types=1);

namespace App\Action;

use App\Service\EventStreamFormatterService;
use App\Service\MessageStorageService;
use App\Service\UsersConnectionsService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Authentication\UserInterface;

abstract class ChatAction implements RequestHandlerInterface
{
    protected $usersConnections;
    protected $messagesStorage;
    protected $eventStreamFormatter;

    public function __construct(
        UsersConnectionsService $usersConnections,
        MessageStorageService $messageStorage,
        EventStreamFormatterService $eventStreamFormatter
    ) {
        $this->usersConnections = $usersConnections;
        $this->messagesStorage = $messageStorage;
        $this->eventStreamFormatter = $eventStreamFormatter;
    }

    protected function getUser(ServerRequestInterface $request): UserInterface
    {
        $user = $request->getAttribute(UserInterface::class, null);
        if (!$user instanceof UserInterface) {
            throw new \LogicException('User not provided. Probably authentication not configured.');
        }

        return $user;
    }
}
