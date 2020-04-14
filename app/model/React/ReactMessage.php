<?php

use FKSDB\Messages\Message;

/**
 * Class ReactMessage
 */
class ReactMessage extends Message {
    /**
     * @param Message $message
     * @return ReactMessage
     */
    public static function createFromMessage(Message $message): ReactMessage {
        return new static($message->getMessage(), $message->getLevel());
    }
}
