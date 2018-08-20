<?php

declare(strict_types=1);

namespace App\Action;

use App\SwooleEventStreamResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Authentication\UserInterface;

class PostAction extends ChatAction
{
    private $inc = 0;

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
        $id = $this->getMid($user);
        $time = \date('Y-m-d H:i:sO');
        $message = [
            'user' => [
                'uid' => $user->getIdentity(),
                'name' => $user->getDetail('name'),
            ],
            'message' => $message,
            'time' => $time,
            'id' => $id,
        ];
        $this->messagesStorage->add($message);

        $this->usersConnections->walk(
            function (SwooleEventStreamResponse $response) use ($message): void {
                $data = [
                    'status' => 'success',
                    'data' => $message,
                ];
                $json = @\json_encode($data);
                if ($json === false) {
                    throw new \LogicException(\json_last_error_msg());
                }
                $eventStreamMessage = $this->eventStreamFormatter->getEventStreamMessage([
                    'event' => 'post',
                    'data' => $json,
                    'id' => $message['id'],
                ]);
                $response->getBody()->write($eventStreamMessage);
            },
            $user->getIdentity()
        );

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'time' => $time,
                'id' => $id,
            ],
        ]);
    }

    private function getMid(UserInterface $user): string
    {
        return \md5(\sprintf('%d:%s:%s', ++$this->inc, $user->getIdentity(), \microtime()));
    }
}
