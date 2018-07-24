<?php

declare(strict_types=1);

namespace App;

use Psr\Http\Message\StreamInterface;
use Swoole\Http\Response as SwooleResponse;
use Zend\Diactoros\Response;

class SwooleEventStreamResponse extends Response
{
    private const PREDEFINED_HEADERS = [
        'Content-Type' => 'application/event-stream',
        'X-Accel-Buffering' => 'no',
    ];

    private static $lowercasedHeaders = [];

    public function __construct()
    {
        parent::__construct(new SwooleEventStream(), 200, self::PREDEFINED_HEADERS);
    }

    public function withHeader($header, $value)
    {
        $this->assertHeader($header);

        return parent::withHeader($header, $value);
    }

    public function withAddedHeader($header, $value)
    {
        $this->assertHeader($header);

        return parent::withAddedHeader($header, $value);
    }

    public function withoutHeader($header)
    {
        $this->assertHeader($header);

        return parent::withoutHeader($header);
    }

    public function withBody(StreamInterface $body): void
    {
        throw new \LogicException(\sprintf('Set body not allowed for "%s" response', self::class));
    }

    public function setSwooleResponse(SwooleResponse $swooleResponse): void
    {
        $stream = $this->getBody();
        if (!$stream instanceof SwooleEventStream) {
            throw new \LogicException(\sprintf('Expected stream to be "%s", "%s" given', SwooleEventStream::class, \get_class($stream)));
        }
        $stream->setSwooleResponse($swooleResponse);
    }

    private function assertHeader($header): void
    {
        $lowercasedHeader = \mb_strtolower($header);
        if (self::$lowercasedHeaders === []) {
            foreach (self::PREDEFINED_HEADERS as $headerName => $_) {
                self::$lowercasedHeaders[\mb_strtolower($headerName)] = true;
            }
        }
        if (\array_key_exists($lowercasedHeader, self::$lowercasedHeaders)) {
            throw new \LogicException(\sprintf('Set header "%s" not allowed for "%s" response', $header, self::class));
        }
    }
}
