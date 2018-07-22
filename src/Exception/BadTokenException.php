<?php

declare(strict_types=1);

namespace App\Exception;

class BadTokenException extends RuntimeException
{
    public function __construct(int $code = 401, ?\Throwable $previous = null)
    {
        parent::__construct('Bad token', $code, $previous);
    }
}
