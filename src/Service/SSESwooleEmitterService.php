<?php

declare(strict_types=1);

namespace App\Service;

use App\Http\SwooleEventStreamResponse;

class SSESwooleEmitterService
{
    public function sendHeaders(SwooleEventStreamResponse $response): void
    {
        $swooleResponse = $response->getSwooleResponse();
        $swooleResponse->status($response->getStatusCode());
        foreach ($response->getHeaders() as $name => $values) {
            $name = $this->filterHeader($name);
            $swooleResponse->header($name, \implode(', ', $values));
        }
    }

    public function sendMessage(SwooleEventStreamResponse $response, string $payload): void
    {
        $payload = \str_replace(["\r\n", "\r"], "\n", $payload);
        $parts = \explode("\n", $payload);
        $parts = \array_map(
            function (string $str): string {
                return \sprintf('data: %s', \trim($str));
            },
            $parts
        );
        $message = \implode("\n", $parts)."\n\n";
        $response->getSwooleResponse()->write($message);
    }

    public function end(SwooleEventStreamResponse $response): void
    {
        $response->getSwooleResponse()->end();
    }

    private function filterHeader(string $header): string
    {
        $parts = \explode('-', $header);
        $parts = \array_map('ucfirst', $parts);

        return \implode('-', $parts);
    }
}
