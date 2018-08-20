<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\UserConnectionNotExistsException;
use App\RequestHandlerSwooleRunner;
use App\SwooleEventStreamResponse;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Authentication\UserInterface;

// todo: close connection on exception
class UsersConnectionsService
{
    private $uidConnectionMap = [];
    private $fdIdMap = [];

    public function addUserConnection(UserInterface $user, SwooleEventStreamResponse $response, ServerRequestInterface $request): void
    {
        $uid = $user->getIdentity();
        $fd = $request->getAttribute(RequestHandlerSwooleRunner::SWOOLE_REQUEST_FD_ATTRIBUTE, null);
        if ($fd === null) {
            throw new \LogicException(\sprintf('Request attribute "%s" not exists', RequestHandlerSwooleRunner::SWOOLE_REQUEST_FD_ATTRIBUTE));
        }
        if (\array_key_exists($uid, $this->uidConnectionMap)) {
            $this->removeConnectionByUser($user);
        }

        $this->uidConnectionMap[$uid] = ['response' => $response, 'fd' => $fd];
        $this->fdIdMap[$fd] = $uid;
    }

    public function removeConnectionByUser(UserInterface $user): void
    {
        $uid = $user->getIdentity();
        if (!\array_key_exists($uid, $this->uidConnectionMap)) {
            throw new UserConnectionNotExistsException($uid);
        }

        $this->end($this->uidConnectionMap[$uid]['response']);
        $fd = $this->uidConnectionMap[$uid]['fd'];
        unset($this->uidConnectionMap[$uid], $this->fdIdMap[$fd]);
    }

    public function removeConnectionByFd(int $fd): void
    {
        if (!\array_key_exists($fd, $this->fdIdMap)) {
            return;
        }

        $uid = $this->fdIdMap[$fd];
        unset($this->uidConnectionMap[$uid], $this->fdIdMap[$fd]);
    }

    public function walk(callable $callback, ?string $exclude = null): void
    {
        foreach ($this->uidConnectionMap as $uid => $data) {
            if ($uid === $exclude) {
                continue;
            }

            $callback($data['response']);
        }
    }

    private function end(SwooleEventStreamResponse $response): void
    {
        $response->getBody()->close();
    }
}
