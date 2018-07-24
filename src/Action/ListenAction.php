<?php

declare(strict_types=1);

namespace App\Action;

use App\SwooleEventStreamResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListenAction extends ChatAction
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = new SwooleEventStreamResponse();
        $user = $this->getUser($request);
        $usersConnections = $this->getUsersConnections();
        $usersConnections->addUserConnection($user, $response, $request);

        return $response;
    }
}
