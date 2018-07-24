<?php

declare(strict_types=1);

namespace App\Container;

use App\RequestHandlerSwooleRunner;
use App\Service\MemoryUsageService;
use App\Service\SwooleEmitterFactoryService;
use App\Service\UsersConnectionsService;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Server as SwooleHttpServer;
use Zend\Expressive\Response\ServerRequestErrorResponseGenerator;
use Zend\ServiceManager\Factory\FactoryInterface;

class RequestHandlerSwooleRunnerFactory implements FactoryInterface
{
    private const DEFAULT_MEMORY_USAGE_TIMEOUT = 10000;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RequestHandlerSwooleRunner
    {
        $config = $container->get('config');
        $debug = $config['debug'] ?? false;
        $memoryUsageInterval = $config['app_memory_usage_interval'] ?? ($debug ? self::DEFAULT_MEMORY_USAGE_TIMEOUT : 0);

        return new RequestHandlerSwooleRunner(
            $container->get('Zend\Expressive\ApplicationPipeline'),
            $container->get(ServerRequestInterface::class),
            $container->get(ServerRequestErrorResponseGenerator::class),
            $container->get(SwooleHttpServer::class),
            $container->get(SwooleEmitterFactoryService::class),
            $container->get(UsersConnectionsService::class),
            $container->get(MemoryUsageService::class),
            $memoryUsageInterval
        );
    }
}
