<?php

declare(strict_types=1);

namespace App\Container;

use App\Service\MessageStorageService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class MessageStorageServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MessageStorageService
    {
        $config = $container->get('config');

        return \array_key_exists('app_store_messages', $config) ?
            new MessageStorageService($config['app_store_messages']) :
            new MessageStorageService()
        ;
    }
}
