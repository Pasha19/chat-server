<?php

declare(strict_types=1);

namespace App\Service;

use App\SwooleEventStreamResponse;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;
use Zend\Expressive\Swoole\SwooleEmitter;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;

class SwooleEmitterFactoryService
{
    public function __invoke(ResponseInterface $response, Response $swooleResponse)
    {
        if (!$response instanceof SwooleEventStreamResponse) {
            return new SwooleEmitter($swooleResponse);
        }

        return new class($swooleResponse) implements EmitterInterface {
            private $swooleResponse;

            public function __construct(Response $swooleResponse)
            {
                $this->swooleResponse = $swooleResponse;
            }

            public function emit(ResponseInterface $response): bool
            {
                if (!$response instanceof SwooleEventStreamResponse) {
                    throw new \LogicException(
                        \sprintf('Expected response to be "%s", "%s" given', SwooleEventStreamResponse::class, \get_class($response))
                    );
                }
                $response->setSwooleResponse($this->swooleResponse);

                $this->swooleResponse->status($response->getStatusCode());
                foreach ($response->getHeaders() as $name => $values) {
                    $name = $this->filterHeader($name);
                    $this->swooleResponse->header($name, \implode(', ', $values));
                }

                return true;
            }

            private function filterHeader(string $header): string
            {
                $parts = \explode('-', $header);
                $parts = \array_map('ucfirst', $parts);

                return \implode('-', $parts);
            }
        };
    }
}
