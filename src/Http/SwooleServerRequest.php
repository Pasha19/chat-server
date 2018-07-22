<?php

declare(strict_types=1);

namespace App\Http;

use Zend\Diactoros\ServerRequest;

class SwooleServerRequest extends ServerRequest
{
    /**
     * @var int
     */
    private $fd;

    public function getFd(): int
    {
        return $this->fd;
    }

    public function setFd(int $fd): void
    {
        $this->fd = $fd;
    }
}
