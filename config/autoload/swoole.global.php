<?php

declare(strict_types=1);

use Zend\Expressive\Swoole\ConfigProvider;

return array_merge((new ConfigProvider())(), [
    'zend-expressive-swoole' => [
        'swoole-http-server' => [
            'host' => getenv('SWOOLE_HOST') ?: '127.0.0.1',
            'port' => (int) getenv('SWOOLE_PORT') ?: 8000,
        ],
    ],
]);
