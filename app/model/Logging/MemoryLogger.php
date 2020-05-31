<?php

namespace FKSDB\Logging;

use FKSDB\Messages\Message;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class MemoryLogger extends StackedLogger {
    /**
     * @var Message[]
     */
    private array $messages = [];

    /**
     *
     * @return Message[]
     */
    public function getMessages(): array {
        return $this->messages;
    }

    public function clear(): void {
        $this->messages = [];
    }

    protected function doLog(Message $message): void {
        $this->messages[] = $message;
    }
}
