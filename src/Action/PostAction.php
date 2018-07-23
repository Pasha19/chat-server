<?php

declare(strict_types=1);

namespace App\Action;

use App\Http\SwooleEventStreamResponse;
use App\Http\SwooleResponseHandler;
use App\Service\SSESwooleEmitterService;
use App\Service\UsersConnectionsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class PostAction extends ChatAction
{
    private $sseSwooleEmitter;

    public function __construct(UsersConnectionsService $usersConnections, SSESwooleEmitterService $sseSwooleEmitter)
    {
        parent::__construct($usersConnections);

        $this->sseSwooleEmitter = $sseSwooleEmitter;
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
        $usersConnections = $this->getUsersConnections();
        $usersConnections->walk(
            function (SwooleResponseHandler $response) use ($message): void {
                if (!$response instanceof SwooleEventStreamResponse) {
                    throw new \LogicException('Bad response type');
                }
                $this->sseSwooleEmitter->sendMessage($response, $message);
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
