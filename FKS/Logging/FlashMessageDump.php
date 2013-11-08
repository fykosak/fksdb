<?php

namespace FKS\Logging;

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
     * @var mixed[]  logger level => flash message type
     */
    private $levelMap;

    function __construct($levelMap) {
        $this->levelMap = $levelMap;
    }

    public function dump(MemoryLogger $logger, Control $control, $clear = true) {
        foreach ($logger->getMessages() as $message) {
            if (!isset($this->levelMap[$message[MemoryLogger::IDX_LEVEL]])) {
                continue;
            }
            $type = $this->levelMap[$message[MemoryLogger::IDX_LEVEL]];
            $control->flashMessage($message[MemoryLogger::IDX_MESSAGE], $type);
        }
        if ($clear) {
            $logger->clear();
        }
    }

}
