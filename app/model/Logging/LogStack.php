<?php

namespace FKSDB\Logging;

use FKSDB\Messages\Message;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class StackedLogger implements ILogger {

    /**
     * @var ILogger
     */
    private $child;

    /**
     * @return ILogger
     */
    public function getChild() {
        return $this->child;
    }

    public function setChild(ILogger $child): void {
        $this->child = $child;
    }

    /**
     * @param Message $message
     * @return void
     */
    final public function log(Message $message) {
        $this->doLog($message);
        if ($this->getChild()) {
            $this->getChild()->log($message);
        }
    }

    abstract protected function doLog(Message $message): void;
}
