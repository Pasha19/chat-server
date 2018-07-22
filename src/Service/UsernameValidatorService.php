<?php

declare(strict_types=1);

namespace App\Service;

class UsernameValidatorService
{
    public function __invoke(string $name)
    {
        return \mb_strlen($name) <= 30 && \preg_match('/^[a-z]{3,}[a-z0-9_]+$/iu', $name);
    }
}
