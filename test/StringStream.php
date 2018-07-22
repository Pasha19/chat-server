<?php

declare(strict_types=1);

namespace App\Test;

abstract class StringStream
{
    public static function create(string $str)
    {
        /** @var resource $fp */
        $fp = \fopen('php://memory', 'w+');
        \fwrite($fp, $str);
        \rewind($fp);

        return $fp;
    }
}
