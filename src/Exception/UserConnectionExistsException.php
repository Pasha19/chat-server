<?php

declare(strict_types=1);

namespace App\Exception;

class UserConnectionExistsException extends RuntimeException
{
    public function __construct(string $uid, int $fd, ?\Throwable $previous = null)
    {
        parent::__construct(\sprintf('Connection for uid "%s" already exists. fd: %d', $uid, $fd), 500, $previous);
    }
}