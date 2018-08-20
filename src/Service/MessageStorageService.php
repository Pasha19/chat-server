<?php

declare(strict_types=1);

namespace App\Service;

class MessageStorageService
{
    private $maxMessages;
    private $messages = [];

    public function __construct(int $maxMessages = 20)
    {
        $this->maxMessages = $maxMessages;
    }

    public function add(array $message): void
    {
        $this->messages[$message['id']] = $message;
        if (\count($this->messages) > $this->maxMessages) {
            \array_shift($this->messages);
        }
    }

    public function getMessagesLaterThan(string $id = ''): array
    {
        if ($id === '') {
            return \array_values($this->messages);
        }

        $messages = [];
        foreach (\array_reverse($this->messages) as $k => $message) {
            if ($k === $id) {
                break;
            }

            $messages[] = $message;
        }

        return \array_reverse($messages);
    }
}
