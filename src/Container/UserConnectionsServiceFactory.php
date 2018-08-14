<?php

declare(strict_types=1);

namespace App\Container;

use App\Service\EventStreamFormatterService;
use App\Service\UsersConnectionsService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class UserConnectionsServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UsersConnectionsService
    {
        return new UsersConnectionsService($container->get(EventStreamFormatterService::class));
    }
}
