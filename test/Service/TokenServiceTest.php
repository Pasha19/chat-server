<?php

declare(strict_types=1);

namespace App\Test\Service;

use App\Exception\BadTokenException;
use App\Service\TokenService;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use PHPUnit\Framework\TestCase;

class TokenServiceTest extends TestCase
{
    private const TEST_SECRET_KEY = 'test_secret_key';
    private const USER_NAME = 'Tester';
    private $tokenService;
    private $parser;

    public function setUp(): void/* The :void return type declaration that should be here would cause a BC issue. FIXME: cs-fixer added void */
    {
        $this->parser = new Parser();
        $this->tokenService = new TokenService(new Builder(), new Sha256(), $this->parser, new ValidationData(), self::TEST_SECRET_KEY);
    }

    public function testGetTokenByName(): string
    {
        $now = \time();
        $strToken = $this->tokenService->getTokenByName(self::USER_NAME);
        $token = $this->parser->parse($strToken);

        $iat = $token->getClaim('iat');
        $this->assertGreaterThanOrEqual($now, $iat);
        $this->assertSame($iat, $token->getClaim('nbf'));
        $this->assertSame($iat + 30 * 24 * 60 * 60, $token->getClaim('exp'));
        $this->assertSame(self::USER_NAME, $token->getClaim('name'));
        $this->assertInternalType('string', $token->getClaim('uid'));

        return $strToken;
    }

    /**
     * @depends testGetTokenByName
     *
     * @param string $strToken
     *
     * @return string
     */
    public function testGetUserByToken(string $strToken): string
    {
        $user = $this->tokenService->getUserByToken($strToken);
        $this->assertSame(self::USER_NAME, $user->getDetail('name'));

        return $strToken;
    }

    public function testExpiredToken(): void
    {
        $this->expectException(BadTokenException::class);

        $strToken = $this->tokenService->getTokenByName(self::USER_NAME, -1);
        $this->tokenService->getUserByToken($strToken);
    }

    public function testTokenWithoutNameAndUid(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        $time = \time();
        $token = (string) (new Builder())
            ->setIssuedAt($time)
            ->setNotBefore($time)
            ->setExpiration($time + 100)
            ->set('foo', 'foo')
            ->sign(new Sha256(), self::TEST_SECRET_KEY)
            ->getToken()
        ;
        $this->testGetUserByToken($token);
    }
}
