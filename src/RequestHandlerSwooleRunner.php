<?php

declare(strict_types=1);

namespace App;

use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Http\Server;
use Zend\Expressive\Swoole\SwooleEmitter;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;
use Zend\HttpHandlerRunner\RequestHandlerRunner;

class RequestHandlerSwooleRunner extends RequestHandlerRunner
{
    private const ORIGINAL_HASH = '1730f6d16b1cd738d3b4d2ae8c75de70';

    private $handler;
    private $serverRequestErrorResponseGenerator;
    private $serverRequestFactory;
    private $swooleHttpServer;

    public function __construct(
        RequestHandlerInterface $handler,
        callable $serverRequestFactory,
        callable $serverRequestErrorResponseGenerator,
        Server $swooleHttpServer
    ) {
        $this->assertOriginalContent();

        $this->handler = $handler;
        $this->serverRequestFactory = \is_object($serverRequestFactory) && $serverRequestFactory instanceof \Closure ?
            $serverRequestFactory :
            \Closure::fromCallable($serverRequestFactory)
        ;
        $this->serverRequestErrorResponseGenerator =
            \is_object($serverRequestErrorResponseGenerator) &&
            $serverRequestErrorResponseGenerator instanceof \Closure ?
                $serverRequestErrorResponseGenerator :
                \Closure::fromCallable($serverRequestErrorResponseGenerator)
        ;
        $this->swooleHttpServer = $swooleHttpServer;
    }

    public function run(): void
    {
        $this->swooleHttpServer->on('start', function ($server): void {
            \printf('Swoole is running at %s:%s%s', $server->host, $server->port, PHP_EOL);
        });

        $this->swooleHttpServer->on('request', function ($request, $response): void {
            \printf(
                '%s - %s - %s %s%s',
                \date('Y-m-d H:i:sO'),
                $request->server['remote_addr'],
                $request->server['request_method'],
                $request->server['request_uri'],
                PHP_EOL
            );
            try {
                $psr7Request = ($this->serverRequestFactory)($request);
            } catch (\Throwable $e) {
                $this->emitMarshalServerRequestException(new SwooleEmitter($response), $e);

                return;
            }
            $psr7Response = $this->handler->handle($psr7Request);
            if (!$psr7Response instanceof SwooleResponseHandler) {
                (new SwooleEmitter($response))->emit($psr7Response);
            } else {
                $psr7Response->setSwooleResponse($response);
            }
        });
        $this->swooleHttpServer->start();
    }

    private function emitMarshalServerRequestException(
        EmitterInterface $emitter,
        \Throwable $exception
    ): void {
        $response = ($this->serverRequestErrorResponseGenerator)($exception);
        $emitter->emit($response);
    }

    private function assertOriginalContent(): void
    {
        $path = __DIR__.'/../vendor/zendframework/zend-expressive-swoole/src/RequestHandlerSwooleRunner.php';
        $content = \file_get_contents($path);
        if ($content === false) {
            throw new \LogicException(\sprintf('File "%s" not found%s', \realpath($path), PHP_EOL));
        }

        $hash = \md5($content);
        if ($hash !== self::ORIGINAL_HASH) {
            throw new \LogicException(\sprintf('Invalid runner hash. Expected "%s", actual "%s"', self::ORIGINAL_HASH, $hash));
        }
    }
}
