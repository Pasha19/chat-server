<?php

declare(strict_types=1);

namespace App\Container;

use App\Action\RegisterAction;
use App\Service\AuthService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class RegisterActionFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RegisterAction
    {
        return new RegisterAction($container->get(AuthService::class));
    }
}
