<?php

declare(strict_types=1);

namespace App\Service;

use App\Data\User;
use App\Exception\BadTokenException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\ValidationData;
use Zend\Expressive\Authentication\UserInterface;

class TokenService
{
    private const SECONDS_IN_MONTH = 2592000;

    private $jwtBuilder;
    private $signer;
    private $parser;
    private $validator;
    private $secret;
    private $inc = 0;

    public function __construct(
        Builder $jwtBuilder,
        Signer $signer,
        Parser $parser,
        ValidationData $validator,
        string $secret
    ) {
        $this->jwtBuilder = $jwtBuilder;
        $this->signer = $signer;
        $this->parser = $parser;
        $this->validator = $validator;
        $this->secret = $secret;
    }

    public function getTokenByName(string $name, int $ttl = self::SECONDS_IN_MONTH): string
    {
        $time = \time();

        $token = $this->jwtBuilder
            ->setIssuedAt($time)
            ->setNotBefore($time)
            ->setExpiration($time + $ttl)
            ->set('name', $name)
            ->set('uid', $this->getUid($name))
            ->sign($this->signer, $this->secret)
            ->getToken()
        ;
        $this->jwtBuilder->unsign();

        return (string) $token;
    }

    public function getUserByToken(string $strToken): UserInterface
    {
        $token = $this->parser->parse($strToken);
        $this->validator->setCurrentTime(\time());
        $valid = $token->validate($this->validator);
        if (!$valid) {
            throw new BadTokenException();
        }

        return new User($token);
    }

    private function getUid(string $name): string
    {
        return \md5(\sprintf('%d:%s:%s', ++$this->inc, $name, \microtime()));
    }
}
