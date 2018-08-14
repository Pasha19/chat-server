<?php

declare(strict_types=1);

namespace App\Container;

use App\Action\ChatAction;
use App\Action\ListenAction;
use App\Service\UsersConnectionsService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ListenActionFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ChatAction
    {
        return new ListenAction($container->get(UsersConnectionsService::class));
    }
}