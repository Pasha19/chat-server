<?php

declare(strict_types=1);

namespace App\Http;

use App\Exception\StreamException;
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Response;

class SwooleEventStreamResponse extends Response implements SwooleResponseHandler
{
    use SwooleResponseHandlerTrait;

    private static $stream;

    public function __construct()
    {
        parent::__construct($this->getStream(), 200, [
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function getStream(): StreamInterface
    {
        if (self::$stream === null) {
            self::$stream = new class() implements StreamInterface {
                public function __toString(): string
                {
                    return '';
                }

                public function close(): void
                {
                }

                public function detach(): void
                {
                }

                public function getSize(): ?int
                {
                    return null;
                }

                public function tell(): void
                {
                    throw new StreamException();
                }

                public function eof(): bool
                {
                    return true;
                }

                public function isSeekable(): bool
                {
                    return false;
                }

                public function seek($offset, $whence = SEEK_SET): bool
                {
                    throw new StreamException();
                }

                public function rewind(): void
                {
                    throw new StreamException();
                }

                public function isWritable(): bool
                {
                    return false;
                }

                public function write($string): int
                {
                    throw new StreamException();
                }

                public function isReadable(): bool
                {
                    return false;
                }

                public function read($length): string
                {
                    throw new StreamException();
                }

                public function getContents(): string
                {
                    return '';
                }

                public function getMetadata($key = null): void
                {
                }
            };
        }

        return self::$stream;
    }
}
