<?php

declare(strict_types=1);

namespace App\Action;

use App\SwooleEventStreamResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class PostAction extends ChatAction
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $json = $request->getParsedBody();
        if (!\is_array($json) || !\array_key_exists('message', $json)) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'reason' => 'message not provided',
                ],
                400
            );
        }
        $message = $json['message'];
        $user = $this->getUser($request);
        $usersConnections = $this->getUsersConnections();
        $usersConnections->walk(
            function (SwooleEventStreamResponse $response) use ($message): void {
                $response->getBody()->write($message);
                echo $message, PHP_EOL;
            },
            $user->getIdentity()
        );

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'send' => 'ok',
            ],
        ]);
    }
}
