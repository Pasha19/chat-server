<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

require __DIR__.'/vendor/autoload.php';

(function (): void {
    /** @var ContainerInterface $container */
    $container = require __DIR__.'/config/container.php';

    $app = $container->get(Application::class);
    $factory = $container->get(MiddlewareFactory::class);

    // Execute programmatic/declarative middleware pipeline and routing
    // configuration statements
    (require __DIR__.'/config/pipeline.php')($app, $factory, $container);
    (require __DIR__.'/config/routes.php')($app, $factory, $container);

    $app->run();
})();
