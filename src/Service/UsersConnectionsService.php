<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\UserConnectionExistsException;
use App\Exception\UserConnectionNotExistsException;
use App\Http\SwooleResponseHandler;
use App\Http\SwooleServerRequest;
use Zend\Expressive\Authentication\UserInterface;

class UsersConnectionsService
{
    private $uidConnectionMap = [];
    private $fdIdMap = [];

    private $sseSwooleEmitter;

    public function __construct(SSESwooleEmitterService $sseSwooleEmitterService)
    {
        $this->sseSwooleEmitter = $sseSwooleEmitterService;
    }

    public function addUserConnection(UserInterface $user, SwooleResponseHandler $response, SwooleServerRequest $request): void
    {
        $uid = $user->getIdentity();
        $fd = $request->getFd();
        if (\array_key_exists($uid, $this->uidConnectionMap)) {
            throw new UserConnectionExistsException($uid, $fd);
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

        $this->sseSwooleEmitter->end($this->uidConnectionMap[$uid]['response']);
        $fd = $this->uidConnectionMap[$uid]['fd'];
        unset($this->uidConnectionMap[$uid], $this->fdIdMap[$fd]);
    }

    public function removeConnectionByFd(int $fd): void
    {
        if (!\array_key_exists($fd, $this->fdIdMap)) {
            return;
        }

        $uid = $this->fdIdMap[$fd];
        $this->sseSwooleEmitter->end($this->uidConnectionMap[$uid]['response']);
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
