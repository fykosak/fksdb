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
     * @param MemoryLogger $logger
     * @param Control $control
     * @param bool $clear
     */
    public static function dump(MemoryLogger $logger, Control $control, bool $clear = true) {
        foreach ($logger->getMessages() as $message) {
            $control->flashMessage($message->getMessage(), $message->getLevel());
        }
        if ($clear) {
            $logger->clear();
        }
    }
}
