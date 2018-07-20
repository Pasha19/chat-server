<?php

declare(strict_types=1);

namespace App;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Server as SwooleHttpServer;
use Zend\Expressive\Response\ServerRequestErrorResponseGenerator;
use Zend\HttpHandlerRunner\RequestHandlerRunner;

class RequestHandlerSwooleRunnerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerRunner
    {
        return new RequestHandlerSwooleRunner(
            $container->get('Zend\Expressive\ApplicationPipeline'),
            $container->get(ServerRequestInterface::class),
            $container->get(ServerRequestErrorResponseGenerator::class),
            $container->get(SwooleHttpServer::class)
        );
    }
}
