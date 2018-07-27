<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\Authentication\AuthenticationMiddleware;
use Zend\Expressive\Handler\NotFoundHandler;
use Zend\Expressive\MiddlewareFactory;
use Zend\Expressive\Router\Middleware\DispatchMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Router\Middleware\MethodNotAllowedMiddleware;
use Zend\Expressive\Router\Middleware\RouteMiddleware;
use Zend\Stratigility\Middleware\ErrorHandler;

/*
 * Setup middleware pipeline:
 */
return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    // The error handler should be the first (most outer) middleware to catch
    // all Exceptions.
    $app->pipe(ErrorHandler::class);

    // Pipe more middleware here that you want to execute on every request:
    // - bootstrapping
    // - pre-conditions
    // - modifications to outgoing responses
    //
    // Piped Middleware may be either callables or service names. Middleware may
    // also be passed as an array; each item in the array must resolve to
    // middleware eventually (i.e., callable or service name).
    //
    // Middleware can be attached to specific paths, allowing you to mix and match
    // applications under a common domain.  The handlers in each middleware
    // attached this way will see a URI with the matched path segment removed.
    //
    // i.e., path of "/api/member/profile" only passes "/member/profile" to $apiMiddleware
    // - $app->pipe('/api', $apiMiddleware);
    // - $app->pipe('/docs', $apiDocMiddleware);
    // - $app->pipe('/files', $filesMiddleware);
    $cors = $container->get('config')['app_cors'] ?? [];
    if ($cors !== []) {
        $app->pipe(
            function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($cors): ResponseInterface {
                $response = $handler->handle($request);
                foreach ($cors as $header => $value) {
                    if (!$response->hasHeader($header)) {
                        $response = $response->withHeader($header, $value);
                    }
                }

                return $response;
            }
        );
    }

    // Register the routing middleware in the middleware pipeline.
    // This middleware registers the Zend\Expressive\Router\RouteResult request attribute.
    $app->pipe(RouteMiddleware::class);

    // The following handle routing failures for common conditions:
    // - HEAD request but no routes answer that method
    // - OPTIONS request but no routes answer that method
    // - method not allowed
    // Order here matters; the MethodNotAllowedMiddleware should be placed
    // after the Implicit*Middleware.
    $app->pipe(ImplicitHeadMiddleware::class);
    $app->pipe(ImplicitOptionsMiddleware::class);
    $app->pipe(MethodNotAllowedMiddleware::class);

    // Add more middleware here that needs to introspect the routing results; this
    // might include:
    //
    // - route-based authentication
    // - route-based validation
    // - etc.
    $app->pipe('/api/chat', AuthenticationMiddleware::class);

    // Register the dispatch middleware in the middleware pipeline
    $app->pipe(DispatchMiddleware::class);

    // At this point, if no Response is returned by any middleware, the
    // NotFoundHandler kicks in; alternately, you can provide other fallback
    // middleware to execute.
    $app->pipe(NotFoundHandler::class);
};
