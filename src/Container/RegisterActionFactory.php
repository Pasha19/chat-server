<?php

declare(strict_types=1);

namespace App\Container;

use App\Action\RegisterAction;
use App\Service\TokenService;
use App\Service\UsernameValidatorService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class RegisterActionFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RegisterAction
    {
        return new RegisterAction($container->get(TokenService::class), $container->get(UsernameValidatorService::class));
    }
}
