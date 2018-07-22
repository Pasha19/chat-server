<?php

declare(strict_types=1);

namespace App\Http;

use Zend\Diactoros\Response\JsonResponse;

class JsonSwooleResponse extends JsonResponse implements SwooleResponseHandler
{
    use SwooleResponseHandlerTrait;
}
