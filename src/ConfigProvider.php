<?php

declare(strict_types=1);

namespace App;

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
            'factories' => [
                RequestHandlerRunner::class => RequestHandlerSwooleRunnerFactory::class,
            ],
            'delegators' => [
                ErrorHandler::class => [
                    ErrorLoggerDelegatorFactory::class,
                ],
            ],
        ];
    }
}
