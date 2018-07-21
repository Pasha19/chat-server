<?php

declare(strict_types=1);

namespace App\Service;

class MemoryUsageService
{
    private const BYTES_IN_MIB = 1048576;
    
    private $memoryUsed = 0;
    private $memoryDiff = 0;
    private $memoryPeek = 0;
    
    public function tick(): void
    {
        $memoryUsage = memory_get_usage(true);
        $this->memoryDiff = $memoryUsage - $this->memoryUsed;
        $this->memoryUsed = $memoryUsage;
        $this->memoryPeek = memory_get_peak_usage(true);
    }

    public function getMemoryUsed(): float
    {
        return (float) $this->memoryUsed / self::BYTES_IN_MIB;
    }

    public function getMemoryDiff(): float
    {
        return (float) $this->memoryDiff / self::BYTES_IN_MIB;
    }

    public function getMemoryPeek(): float
    {
        return (float) $this->memoryPeek / self::BYTES_IN_MIB;
    }
}
