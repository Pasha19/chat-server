<?php

declare(strict_types=1);

namespace App\Container;

use App\Action\ChatAction;
use App\Action\PostAction;
use App\Service\EventStreamFormatterService;
use App\Service\UsersConnectionsService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class PostActionFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ChatAction
    {
        return new PostAction(
            $container->get(UsersConnectionsService::class),
            $container->get(EventStreamFormatterService::class)
        );
    }
}
