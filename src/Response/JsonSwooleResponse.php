<?php

declare(strict_types=1);

namespace App\Response;

use Zend\Diactoros\Response\JsonResponse;

class JsonSwooleResponse extends JsonResponse implements SwooleResponseHandler
{
    use SwooleResponseHandlerTrait;
}
