<?php

declare(strict_types=1);

namespace App;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;

interface SwooleResponseHandler extends ResponseInterface
{
    public function setSwooleResponse(Response $swooleResponse): void;

    public function getSwooleResponse(): Response;
}
