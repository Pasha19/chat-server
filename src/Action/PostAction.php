<?php

declare(strict_types=1);

namespace App\Action;

use App\Service\EventStreamFormatterService;
use App\Service\UsersConnectionsService;
use App\SwooleEventStreamResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Authentication\UserInterface;

class PostAction extends ChatAction
{
    private $eventStreamFormatter;
    private $inc = 0;

    public function __construct(UsersConnectionsService $usersConnections, EventStreamFormatterService $eventStreamFormatter)
    {
        parent::__construct($usersConnections);

        $this->eventStreamFormatter = $eventStreamFormatter;
    }

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

        $usersConnections = $this->getUsersConnections();
        $usersConnections->walk(
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
            ],
        ]);
    }

    private function getMid(UserInterface $user): string
    {
        return \md5(\sprintf('%d:%s:%s', ++$this->inc, $user->getIdentity(), \microtime()));
    }
}
