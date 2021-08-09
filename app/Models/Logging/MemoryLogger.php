<?php

declare(strict_types=1);

namespace FKSDB\Models\Logging;

use FKSDB\Models\Messages\Message;

class MemoryLogger extends StackedLogger
{

    private array $messages = [];

    /**
     * @return Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function clear(): void
    {
        $this->messages = [];
    }

    protected function doLog(Message $message): void
    {
        $this->messages[] = $message;
    }
}
