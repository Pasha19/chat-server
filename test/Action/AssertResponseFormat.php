<?php

declare(strict_types=1);

namespace App\Test\Action;

trait AssertResponseFormat
{
    private function assertResponseFormat(string $rawBody): array
    {
        $this->assertJson($rawBody);
        $json = \json_decode($rawBody, true);
        $this->assertInternalType('array', $json);
        $this->assertArrayHasKey('status', $json);
        $this->assertCount(2, $json);

        return $json;
    }

    private function assertSuccessResponseFormat(string $rawBody): array
    {
        $json = $this->assertResponseFormat($rawBody);
        $this->assertSame('success', $json['status']);
        $this->assertArrayHasKey('data', $json);

        return $json['data'];
    }

    private function assertErrorResponseFormat(string $rawBody, string $reason): void
    {
        $json = $this->assertResponseFormat($rawBody);
        $this->assertSame('error', $json['status']);
        $this->assertArrayHasKey('reason', $json);
        $this->assertSame($reason, $json['reason']);
    }
}
