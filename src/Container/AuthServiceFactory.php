<?php

declare(strict_types=1);

namespace App\Container;

use App\Service\AuthService;
use App\Service\TokenService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class AuthServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AuthService
    {
        return new AuthService($container->get(TokenService::class));
    }
}
