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
    private $messages = [];

    /**
     *
     * @return Message[]
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * @return void
     */
    public function clear() {
        $this->messages = [];
    }

    /**
     * @param Message $message
     * @return void
     */
    protected function doLog(Message $message) {
        $this->messages[] = $message;
    }

}
