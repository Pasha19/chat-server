<?php

declare(strict_types=1);

namespace App\Exception;

class UserConnectionNotExistsException extends RuntimeException
{
    public function __construct(string $uid, int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct(\sprintf('Connection for uid "%s" not found', $uid), $code, $previous);
    }
}
