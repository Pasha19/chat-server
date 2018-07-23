<?php

declare(strict_types=1);

namespace App\Action;

use App\Http\JsonSwooleResponse;
use App\Http\SwooleServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListenAction extends ChatAction
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = new JsonSwooleResponse('in process...');
        $user = $this->getUser($request);
        $swooleRequest = $this->getSwooleRequest($request);
        $usersConnections = $this->getUsersConnections();
        $usersConnections->addUserConnection($user, $response, $swooleRequest);

        return $response;
    }

    private function getSwooleRequest(ServerRequestInterface $request): SwooleServerRequest
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

        return $request;
    }
}
