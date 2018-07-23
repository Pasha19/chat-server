<?php

declare(strict_types=1);

namespace App\Container;

use App\Service\TokenService;
use Interop\Container\ContainerInterface;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\ValidationData;
use Zend\ServiceManager\Factory\FactoryInterface;

class TokenServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TokenService
    {
        return new TokenService(
            $container->get(Builder::class),
            $container->get(Signer::class),
            $container->get(Parser::class),
            $container->get(ValidationData::class),
            $container->get('config')['app_secret']
        );
    }
}
