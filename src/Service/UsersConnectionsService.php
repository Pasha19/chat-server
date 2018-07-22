<?php

declare(strict_types=1);

namespace App\Service;

use App\Data\User;
use App\Exception\UserConnectionExistsException;
use App\Exception\UserConnectionNotExistsException;
use App\Http\SwooleResponseHandler;
use App\Http\SwooleServerRequest;

class UsersConnectionsService
{
    private $uidConnectionMap = [];
    private $fdIdMap = [];

    public function addUserConnection(User $user, SwooleResponseHandler $response, SwooleServerRequest $request): void
    {
        $uid = $user->getUid();
        $fd = $request->getFd();
        if (\array_key_exists($uid, $this->uidConnectionMap)) {
            throw new UserConnectionExistsException($uid, $fd);
        }

        $this->uidConnectionMap[$uid] = ['response' => $response, 'fd' => $fd];
        $this->fdIdMap[$fd] = $uid;
    }

    public function removeConnectionByUser(User $user): void
    {
        $uid = $user->getUid();
        if (!\array_key_exists($uid, $this->uidConnectionMap)) {
            throw new UserConnectionNotExistsException($uid);
        }

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
}
