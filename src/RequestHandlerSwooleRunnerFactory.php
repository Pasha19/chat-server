<?php

declare(strict_types=1);

namespace App;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Server as SwooleHttpServer;
use Zend\Expressive\Response\ServerRequestErrorResponseGenerator;
use Zend\ServiceManager\Factory\FactoryInterface;

class RequestHandlerSwooleRunnerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new RequestHandlerSwooleRunner(
            $container->get('Zend\Expressive\ApplicationPipeline'),
            $container->get(ServerRequestInterface::class),
            $container->get(ServerRequestErrorResponseGenerator::class),
            $container->get(SwooleHttpServer::class)
        );
    }
}
