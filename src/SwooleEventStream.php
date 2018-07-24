<?php

declare(strict_types=1);

namespace App;

use App\Exception\EventStreamException;
use Psr\Http\Message\StreamInterface;
use Swoole\Http\Response;

class SwooleEventStream implements StreamInterface
{
    /**
     * @var Response;
     */
    private $swooleResponse;
    private $buffer = [];

    public function __toString()
    {
        return "\u{1F4A9}";
    }

    public function close(): void
    {
        if ($this->swooleResponse === null) {
            throw new EventStreamException('No response object provided');
        }
        $this->swooleResponse->end();
    }

    public function detach(): void
    {
        throw new EventStreamException('Event stream cant be detached');
    }

    public function getSize(): ?int
    {
        return null;
    }

    public function tell(): int
    {
        return 0;
    }

    public function eof(): bool
    {
        return true;
    }

    public function isSeekable()
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new EventStreamException('Event stream is not seekable');
    }

    public function rewind(): void
    {
        throw new EventStreamException('Event stream cant be rewind');
    }

    public function isWritable()
    {
        return true;
    }

    public function write($string): int
    {
        $string = $this->prepareString($string);
        $bytes = \mb_strlen($string, '8bit');
        if ($this->swooleResponse === null) {
            $buffer[] = $string;
        } else {
            $this->doWrite($string);
        }

        return $bytes;
    }

    public function isReadable()
    {
        return false;
    }

    public function read($length): void
    {
        throw new EventStreamException('Event stream is not readable');
    }

    public function getContents(): void
    {
        throw new EventStreamException('Event stream is not readable');
    }

    public function getMetadata($key = null): void
    {
    }

    public function setSwooleResponse(Response $swooleResponse): void
    {
        $this->swooleResponse = $swooleResponse;
        while ($this->buffer !== []) {
            $this->doWrite(\array_pop($this->buffer));
        }
    }

    private function prepareString(string $string): string
    {
        $string = \str_replace(["\r\n", "\r"], "\n", $string);
        $parts = \explode("\n", $string);
        $parts = \array_map(
            function (string $s): string {
                return 'data: '.\trim($s);
            },
            $parts
        );

        return \implode("\n", $parts)."\n\n";
    }

    private function doWrite(string $string): void
    {
        // TODO: chunks write
        $this->swooleResponse->write($string);
    }
}
