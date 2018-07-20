<?php

declare(strict_types=1);

namespace App;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;
use Zend\Stratigility\Middleware\ErrorHandler;

class ErrorLoggerDelegatorFactory implements DelegatorFactoryInterface
{
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        /** @var ErrorHandler $errorHandler */
        $errorHandler = $callback();
        $errorHandler->attachListener(
            function (\Throwable $error, ServerRequestInterface $request, ResponseInterface $response): void {
                $message = \sprintf(
                    'Error: %s Request: %s %s',
                    $this->prepareStackTrace($error),
                    $request->getMethod(),
                    $request->getRequestTarget()
                );
                // TODO use smth async
                \error_log($message);
            }
        );

        return $errorHandler;
    }

    private function prepareStackTrace(\Throwable $error): string
    {
        $message = '';
        do {
            $message .= \sprintf(
                '%s "%s" at %s:%d ',
                \get_class($error),
                $error->getMessage(),
                $error->getFile(),
                $error->getLine()
            );
        } while ($error = $error->getPrevious());

        return $message;
    }
}
