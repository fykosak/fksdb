<?php

use FKSDB\Messages\Message;

/**
 * Class ReactMessage
 */
class ReactMessage extends Message {

    public static function createFromMessage(Message $message): ReactMessage {
        return new static($message->getMessage(), $message->getLevel());
    }
}
