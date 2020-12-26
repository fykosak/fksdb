<?php

namespace FKSDB\Models\Logging;

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

    public static function dump(ILogger $logger, Control $control, bool $clear = true): void {
        if ($logger instanceof MemoryLogger) {
            foreach ($logger->getMessages() as $message) {
                $control->flashMessage($message->text, $message->level);
            }
            if ($clear) {
                $logger->clear();
            }
        }
    }
}
