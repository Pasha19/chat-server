<?php

declare(strict_types=1);

namespace App;

use App\Action\ListenAction;
use App\Action\PostAction;
use App\Action\RegisterAction;
use App\Container\AuthServiceFactory;
use App\Container\ChatActionFactory;
use App\Container\ErrorLoggerDelegator;
use App\Container\MessageStorageServiceFactory;
use App\Container\RegisterActionFactory;
use App\Container\RequestHandlerSwooleRunnerFactory;
use App\Container\TokenServiceFactory;
use App\Service\AuthService;
use App\Service\EventStreamFormatterService;
use App\Service\MemoryUsageService;
use App\Service\MessageStorageService;
use App\Service\SwooleEmitterFactoryService;
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
                Builder::class,
                EventStreamFormatterService::class,
                MemoryUsageService::class,
                Parser::class,
                Signer::class => Sha256::class,
                SwooleEmitterFactoryService::class,
                UsernameValidatorService::class,
                UsersConnectionsService::class,
                ValidationData::class,
            ],
            'factories' => [
                AuthService::class => AuthServiceFactory::class,
                ListenAction::class => ChatActionFactory::class,
                MessageStorageService::class => MessageStorageServiceFactory::class,
                PostAction::class => ChatActionFactory::class,
                RegisterAction::class => RegisterActionFactory::class,
                RequestHandlerRunner::class => RequestHandlerSwooleRunnerFactory::class,
                TokenService::class => TokenServiceFactory::class,
            ],
            'delegators' => [
                ErrorHandler::class => [
                    ErrorLoggerDelegator::class,
                ],
            ],
        ];
    }
}
