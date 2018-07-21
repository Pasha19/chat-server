<?php

declare(strict_types=1);

namespace App\Response;

use Swoole\Http\Response;

trait SwooleResponseHandlerTrait
{
    private $swooleResponse;

    public function setSwooleResponse(Response $swooleResponse): void
    {
        $this->swooleResponse = $swooleResponse;
    }

    public function getSwooleResponse(): Response
    {
        return $this->swooleResponse;
    }
}
