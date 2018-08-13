<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\EventStreamException;

class EventStreamFormatterService
{
    public function getEventStreamMessage(array $data): string
    {
        $result = '';
        if (\array_key_exists('event', $data)) {
            $result .= "event: {$data['event']}\n";
        }
        if (!\array_key_exists('data', $data)) {
            throw new EventStreamException('Event stream message requires "data"');
        }
        $result .= $this->prepareDataString($data['data'])."\n";
        if (\array_key_exists('id', $data)) {
            $result .= "id: {$data['id']}\n";
        }
        $result .= "\n";

        return $result;
    }

    private function prepareDataString(string $data): string
    {
        $data = \str_replace(["\r\n", "\r"], "\n", $data);
        $parts = \explode("\n", $data);
        $parts = \array_map(
            function (string $s): string {
                return 'data: '.\trim($s);
            },
            $parts
        );

        return \implode("\n", $parts);
    }
}
