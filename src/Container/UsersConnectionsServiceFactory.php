<?php

declare(strict_types=1);

namespace App\Container;

use App\Service\SSESwooleEmitterService;
use App\Service\UsersConnectionsService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class UsersConnectionsServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UsersConnectionsService
    {
        return new UsersConnectionsService($container->get(SSESwooleEmitterService::class));
    }
}
