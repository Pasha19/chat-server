<?php

declare(strict_types=1);

namespace App\Action;

use App\Http\JsonSwooleResponse;
use App\Http\SwooleServerRequest;
use App\Service\UsersConnectionsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Authentication\UserInterface;

class ListenAction implements RequestHandlerInterface
{
    private $usersConnection;

    public function __construct(UsersConnectionsService $usersConnections)
    {
        $this->usersConnection = $usersConnections;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$request instanceof SwooleServerRequest) {
            throw new \LogicException(
                \sprintf(
                    'Expected response to be instance of "%s", "%s" given. Probably swoole runner not configured',
                    SwooleServerRequest::class,
                    \get_class($request)
                )
            );
        }
        $user = $request->getAttribute(UserInterface::class, null);
        if (!$user instanceof UserInterface) {
            throw new \LogicException('User not provided. Probably authentication not configured.');
        }
        $response = new JsonSwooleResponse('in process...');
        $this->usersConnection->addUserConnection($user, $response, $request);

        return $response;
    }
}
