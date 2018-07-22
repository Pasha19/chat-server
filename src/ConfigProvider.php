<?php

declare(strict_types=1);

namespace App;

use App\Action\RegisterAction;
use App\Container\AuthServiceFactory;
use App\Container\ErrorLoggerDelegator;
use App\Container\RegisterActionFactory;
use App\Container\RequestHandlerSwooleRunnerFactory;
use App\Container\ServerRequestSwooleFactory;
use App\Service\AuthService;
use App\Service\MemoryUsageService;
use App\Service\UsernameValidatorService;
use App\Service\UsersConnectionsService;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Psr\Http\Message\ServerRequestInterface;
use Zend\HttpHandlerRunner\RequestHandlerRunner;
use Zend\Stratigility\Middleware\ErrorHandler;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'invokables' => [
                MemoryUsageService::class,
                Builder::class,
                Signer::class => Sha256::class,
                Parser::class,
                ValidationData::class,
                UsernameValidatorService::class,
                UsersConnectionsService::class,
            ],
            'factories' => [
                RequestHandlerRunner::class => RequestHandlerSwooleRunnerFactory::class,
                ServerRequestInterface::class => ServerRequestSwooleFactory::class,
                AuthService::class => AuthServiceFactory::class,
                RegisterAction::class => RegisterActionFactory::class,
            ],
            'delegators' => [
                ErrorHandler::class => [
                    ErrorLoggerDelegator::class,
                ],
            ],
        ];
    }
}
