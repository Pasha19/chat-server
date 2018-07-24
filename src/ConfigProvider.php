<?php

declare(strict_types=1);

namespace App;

use App\Action\ListenAction;
use App\Action\PostAction;
use App\Action\RegisterAction;
use App\Container\AuthServiceFactory;
use App\Container\ErrorLoggerDelegator;
use App\Container\ListenActionFactory;
use App\Container\PostActionFactory;
use App\Container\RegisterActionFactory;
use App\Container\RequestHandlerSwooleRunnerFactory;
use App\Container\TokenServiceFactory;
use App\Container\UsersConnectionsServiceFactory;
use App\Service\AuthService;
use App\Service\MemoryUsageService;
use App\Service\SSESwooleEmitterService;
use App\Service\TokenService;
use App\Service\UsernameValidatorService;
use App\Service\UsersConnectionsService;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
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
                SSESwooleEmitterService::class,
            ],
            'factories' => [
                RequestHandlerRunner::class => RequestHandlerSwooleRunnerFactory::class,
                TokenService::class => TokenServiceFactory::class,
                RegisterAction::class => RegisterActionFactory::class,
                AuthService::class => AuthServiceFactory::class,
                ListenAction::class => ListenActionFactory::class,
                PostAction::class => PostActionFactory::class,
                UsersConnectionsService::class => UsersConnectionsServiceFactory::class,
            ],
            'delegators' => [
                ErrorHandler::class => [
                    ErrorLoggerDelegator::class,
                ],
            ],
        ];
    }
}
