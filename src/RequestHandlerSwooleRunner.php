<?php

declare(strict_types=1);

namespace App;

use App\Http\SwooleResponseHandler;
use App\Http\SwooleServerRequest;
use App\Service\MemoryUsageService;
use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Zend\Expressive\Swoole\SwooleEmitter;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;
use Zend\HttpHandlerRunner\RequestHandlerRunner;

class RequestHandlerSwooleRunner extends RequestHandlerRunner
{
    private $handler;
    private $serverRequestErrorResponseGenerator;
    private $serverRequestFactory;
    private $swooleHttpServer;
    private $memoryUsageService;
    private $memoryUsageInteval;

    public function __construct(
        RequestHandlerInterface $handler,
        callable $serverRequestFactory,
        callable $serverRequestErrorResponseGenerator,
        Server $swooleHttpServer,
        MemoryUsageService $memoryUsageService,
        int $memoryUsageInterval
    ) {
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
        $this->memoryUsageService = $memoryUsageService;
        $this->memoryUsageInteval = $memoryUsageInterval;
    }

    public function run(): void
    {
        $this->swooleHttpServer->on('start', function (Server $server): void {
            \printf('Swoole is running at %s:%s%s', $server->host, $server->port, PHP_EOL);
        });

        $this->swooleHttpServer->on('request', function (Request $request, Response $response): void {
            \printf(
                '[%s] - %s - %s %s%s',
                \date('Y-m-d H:i:sO'),
                $request->server['remote_addr'],
                $request->server['request_method'],
                $request->server['request_uri'],
                PHP_EOL
            );
            try {
                /** @var SwooleServerRequest $psr7Request */
                $psr7Request = ($this->serverRequestFactory)($request);
                $psr7Request->setFd($request->fd);
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

        $timerId = $this->getMemoryUsageTimer();

        $this->swooleHttpServer->start();

        if ($timerId !== 0) {
            $this->swooleHttpServer->clearTimer($timerId);
        }
    }

    private function emitMarshalServerRequestException(
        EmitterInterface $emitter,
        \Throwable $exception
    ): void {
        $response = ($this->serverRequestErrorResponseGenerator)($exception);
        $emitter->emit($response);
    }

    private function &getMemoryUsageTimer(): int
    {
        $timerId = 0;
        if ($this->memoryUsageInteval > 0) {
            $this->swooleHttpServer->on('workerStart', function () use (&$timerId): void {
                $timerId = $this->swooleHttpServer->tick($this->memoryUsageInteval, function (): void {
                    ($this->memoryUsageService)();
                    \printf(
                        '[%s] - Memory usage: %.3f MiB (%+.3f MiB) Peek usage: %.3f MiB%s',
                        \date('Y-m-d H:i:sO'),
                        $this->memoryUsageService->getMemoryUsed(),
                        $this->memoryUsageService->getMemoryDiff(),
                        $this->memoryUsageService->getMemoryPeek(),
                        PHP_EOL
                    );
                });
            });
        }

        return $timerId;
    }
}
