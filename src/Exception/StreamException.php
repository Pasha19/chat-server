<?php

declare(strict_types=1);

namespace App\Exception;

use Throwable;

class StreamException extends RuntimeException
{
    public function __construct(string $message = 'SSE stream not implemented', int $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
