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
        $time = \date('Y-m-d H:i:sO');
        $usersConnections->walk(
            function (SwooleEventStreamResponse $response) use ($message, $user, $time): void {
                $data = [
                    'event' => 'message',
                    'data' => [
                        'user' => [
                            'uid' => $user->getIdentity(),
                            'name' => $user->getDetail('name'),
                        ],
                        'message' => $message,
                        'time' => $time,
                    ],
                ];
                $json = @\json_encode($data);
                if ($json === false) {
                    throw new \LogicException(\json_last_error_msg());
                }
                $response->getBody()->write($json);
            },
            $user->getIdentity()
        );

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'time' => $time,
            ],
        ]);
    }
}
