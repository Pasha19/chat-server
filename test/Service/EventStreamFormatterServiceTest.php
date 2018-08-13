<?php

declare(strict_types=1);

namespace App\Test\Service;

use App\Service\EventStreamFormatterService;
use PHPUnit\Framework\TestCase;

class EventStreamFormatterServiceTest extends TestCase
{
    public function testGetEventStreamMessage(): void
    {
        $eventStreamFormatter = new EventStreamFormatterService();
        $result = $eventStreamFormatter->getEventStreamMessage([
            'event' => 'test',
            'data' => "str\nstr2\rstr3\r\nstr4",
            'id' => 'id',
        ]);
        $this->assertSame("event: test\ndata: str\ndata: str2\ndata: str3\ndata: str4\nid: id\n\n", $result);
    }

    /**
     * @expectedException \App\Exception\EventStreamException
     */
    public function testGetEventStreamMessageWithoutData(): void
    {
        $eventStreamFormatter = new EventStreamFormatterService();
        $eventStreamFormatter->getEventStreamMessage([
            'event' => 'test',
            'id' => 'id',
        ]);
    }
}
