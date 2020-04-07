<?php

namespace FKSDB\Logging;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class MemoryLogger extends StackedLogger {

    const IDX_MESSAGE = 'msg';
    const IDX_LEVEL = 'lvl';

    private $messages = [];

    /**
     *
     * @return array[]  IDX_MESSAGE => message, IDX_LEVEL => level
     */
    public function getMessages() {
        return $this->messages;
    }

    public function clear() {
        $this->messages = [];
    }

    /**
     * @param $message
     * @param $level
     * @return mixed|void
     */
    protected function doLog($message, $level) {
        $this->messages[] = [
            self::IDX_MESSAGE => $message,
            self::IDX_LEVEL => $level,
        ];
    }

}
