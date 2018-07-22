<?php

declare(strict_types=1);

namespace App\Test\Service;

use App\Service\UsernameValidatorService;
use PHPUnit\Framework\TestCase;

class UsernameValidatorServiceTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     *
     * @param string $username
     * @param bool   $result
     */
    public function testValidate(string $username, bool $result): void
    {
        $validator = new UsernameValidatorService();
        $this->assertSame($result, $validator($username));
    }

    public function dataProvider(): array
    {
        return [
            ['username' => 'Abc', 'result' => false],
            ['username' => 'A1bc', 'result' => false],
            ['username' => 'Abc:12', 'result' => false],
            ['username' => 'abc_12', 'result' => true],
            ['username' => \str_repeat('a', 31), 'result' => false],
            ['username' => 'Abc4e', 'result' => true],
            ['username' => \str_repeat('a', 30), 'result' => true],
        ];
    }
}
