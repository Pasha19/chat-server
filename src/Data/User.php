<?php

declare(strict_types=1);

namespace App\Data;

use Lcobucci\JWT\Token;
use Zend\Expressive\Authentication\UserInterface;

class User implements UserInterface
{
    private const ROLE = 'ROLE_CHAT_USER';

    private $token;

    public function __construct(Token $token)
    {
        $this->token = $token;
    }

    public function getIdentity(): string
    {
        return $this->token->getClaim('uid');
    }

    public function getRoles(): array
    {
        return [self::ROLE];
    }

    public function getDetail(string $name, $default = null)
    {
        return $this->token->getClaim($name, $default);
    }

    public function getDetails(): array
    {
        return $this->token->getClaims();
    }
}
