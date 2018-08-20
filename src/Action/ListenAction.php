<?php

declare(strict_types=1);

namespace App\Action;

use App\SwooleEventStreamResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListenAction extends ChatAction
{
    public const LAST_EVENT_ID_PARAM = 'lastEventId';

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // todo: factory for response
        $response = new SwooleEventStreamResponse();
        $user = $this->getUser($request);
        $this->usersConnections->addUserConnection($user, $response, $request);

        $messages = $this->messagesStorage->getMessagesLaterThan(
            $request->getQueryParams()[self::LAST_EVENT_ID_PARAM] ?? ''
        );
        $data = [
            'event' => 'connect',
            'data' => \json_encode([
                'status' => 'success',
                'data' => $messages,
            ]),
        ];
        if ($messages !== []) {
            $lastMessage = \array_values(\array_slice($messages, -1))[0];
            $data['id'] = $lastMessage['id'];
        }

        $response->getBody()->write($this->eventStreamFormatter->getEventStreamMessage($data));

        return $response;
    }
}
