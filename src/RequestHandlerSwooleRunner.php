<?php

declare(strict_types=1);

namespace App;

use App\Http\SwooleEventStreamResponse;
use App\Http\SwooleResponseHandler;
use App\Service\MemoryUsageService;
use App\Service\SSESwooleEmitterService;
use App\Service\UsersConnectionsService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Zend\Expressive\Swoole\SwooleEmitter;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;
use Zend\HttpHandlerRunner\RequestHandlerRunner;

class RequestHandlerSwooleRunner extends RequestHandlerRunner
{
    public const SWOOLE_REQUEST_FD_ATTRIBUTE = 'SWOOLE_REQUEST_FD_ATTRIBUTE';

    private $handler;
    private $serverRequestErrorResponseGenerator;
    private $serverRequestFactory;
    private $swooleHttpServer;
    private $swooleSSEEmitter;
    private $memoryUsageService;
    private $usersConnections;
    private $memoryUsageInteval;

    public function __construct(
        RequestHandlerInterface $handler,
        callable $serverRequestFactory,
        callable $serverRequestErrorResponseGenerator,
        Server $swooleHttpServer,
        SSESwooleEmitterService $swooleSSEEmitter,
        UsersConnectionsService $userConnections,
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
        $this->swooleSSEEmitter = $swooleSSEEmitter;
        $this->usersConnections = $userConnections;
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
                /** @var ServerRequestInterface $psr7Request */
                $psr7Request = ($this->serverRequestFactory)($request);
                $psr7Request = $psr7Request->withAttribute(self::SWOOLE_REQUEST_FD_ATTRIBUTE, $request->fd);
            } catch (\Throwable $e) {
                $this->emitMarshalServerRequestException(new SwooleEmitter($response), $e);

                return;
            }
            $psr7Response = $this->handler->handle($psr7Request);
            if (!$psr7Response instanceof SwooleResponseHandler) {
                (new SwooleEmitter($response))->emit($psr7Response);
            } else {
                $psr7Response->setSwooleResponse($response);
                if ($psr7Response instanceof SwooleEventStreamResponse) {
                    $this->swooleSSEEmitter->sendHeaders($psr7Response);
                }
            }
        });

        $this->swooleHttpServer->on('close', function (Server $server, int $fd): void {
            $this->usersConnections->removeConnectionByFd($fd);
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
