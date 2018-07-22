<?php

declare(strict_types=1);

namespace App\Container;

use App\Http\SwooleServerRequest;
use Interop\Container\ContainerInterface;
use Swoole\Http\Request;
use Zend\Expressive\Swoole\SwooleStream;
use Zend\ServiceManager\Factory\FactoryInterface;
use function Zend\Diactoros\marshalMethodFromSapi;
use function Zend\Diactoros\marshalProtocolVersionFromSapi;
use function Zend\Diactoros\marshalUriFromSapi;
use function Zend\Diactoros\normalizeUploadedFiles;

class ServerRequestSwooleFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): \Closure
    {
        return function (Request $request) {
            // Aggregate values from Swoole request object
            $get = $request->get ?? [];
            $post = $request->post ?? [];
            $cookie = $request->cookie ?? [];
            $files = $request->files ?? [];
            $server = $request->server ?? [];
            $headers = $request->header ?? [];

            // Normalize SAPI params
            $server = \array_change_key_case($server, CASE_UPPER);

            return new SwooleServerRequest(
                $server,
                normalizeUploadedFiles($files),
                marshalUriFromSapi($server, $headers),
                marshalMethodFromSapi($server),
                new SwooleStream($request),
                $headers,
                $cookie,
                $get,
                $post,
                marshalProtocolVersionFromSapi($server)
            );
        };
    }
}
