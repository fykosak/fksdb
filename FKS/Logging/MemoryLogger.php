<?php

namespace FKS\Logging;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class MemoryLogger extends StackedLogger {

    const IDX_MESSAGE = 'msg';
    const IDX_LEVEL = 'lvl';

    private $messages = array();

    /**
     * 
     * @return array[]  IDX_MESSAGE => message, IDX_LEVEL => level
     */
    public function getMessages() {
        return $this->messages;
    }

    public function clear() {
        $this->messages = array();
    }

    protected function doLog($message, $level) {
        $this->messages[] = array(
            self::IDX_MESSAGE => $message,
            self::IDX_LEVEL => $level,
        );
    }

}
