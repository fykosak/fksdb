<?php

namespace FKSDB\Logging;

use FKSDB\Messages\Message;
use Nette\Application\UI\Control;

/**
 * Dump messages from MemoryLogger as flash messaged into given control.
 *
 * @note If mapping from ILogger level to flash message type is not specified,
 * message is ignored.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class FlashMessageDump {
    /**
     * @param ILogger $logger
     * @param Control $control
     * @param bool $clear
     */
    public static function dump(ILogger $logger, Control $control, bool $clear = true) {
        foreach ($logger->getMessages() as $message) {
            if ($message instanceof Message) {
                $control->flashMessage($message->getMessage(), $message->getLevel());
            } else {
                $control->flashMessage($message[MemoryLogger::IDX_MESSAGE], $message[MemoryLogger::IDX_LEVEL]);
            }
        }
        if ($clear) {
            $logger->clear();
        }
    }
}
