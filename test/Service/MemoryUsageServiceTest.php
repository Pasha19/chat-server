<?php

declare(strict_types=1);

namespace App\Test\Service;

use App\Service\MemoryUsageService;
use PHPUnit\Framework\TestCase;

class MemoryUsageServiceTest extends TestCase
{
    public function test(): void
    {
        $memoryUsageService = new MemoryUsageService();
        $this->assertSame(0.0, $memoryUsageService->getMemoryUsed());
        $this->assertSame(0.0, $memoryUsageService->getMemoryDiff());
        $this->assertSame(0.0, $memoryUsageService->getMemoryPeek());
        $memoryUsageService->tick();
        $memoryUsed = $memoryUsageService->getMemoryUsed();
        $memoryDiff = $memoryUsageService->getMemoryDiff();
        $memoryPeek = $memoryUsageService->getMemoryPeek();
        $this->assertGreaterThan(0.0, $memoryUsed);
        $this->assertSame($memoryUsed, $memoryDiff);
        $this->assertGreaterThanOrEqual($memoryUsed, $memoryPeek);
        for ($i = 0, $str = 'a'; $i < 25; ++$i) {
            $str .= $str;
        }
        $memoryUsageService->tick();
        $newMemoryUsed = $memoryUsageService->getMemoryUsed();
        $newMemoryDiff = $memoryUsageService->getMemoryDiff();
        $newMemoryPeek = $memoryUsageService->getMemoryPeek();
        $this->assertGreaterThan($memoryUsed, $newMemoryUsed);
        $this->assertSame($newMemoryUsed, $memoryUsed + $newMemoryDiff);
        $this->assertGreaterThanOrEqual($memoryPeek, $newMemoryPeek);
    }
}
