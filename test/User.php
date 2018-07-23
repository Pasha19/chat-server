<?php

declare(strict_types=1);

namespace App\Test;

use Zend\Expressive\Authentication\UserInterface;

class User implements UserInterface
{
    private $name;
    private $uid;

    public function __construct(string $name, string $uid)
    {
        $this->name = $name;
        $this->uid = $uid;
    }

    public function getIdentity(): string
    {
        return $this->uid;
    }

    public function getRoles(): array
    {
        return ['ROLE_TESTER'];
    }

    public function getDetail(string $name, $default = null)
    {
        return $this->getDetails()[$name] ?? $default;
    }

    public function getDetails(): array
    {
        return [
            'name' => $this->name,
            'uid' => $this->uid,
        ];
    }
}
