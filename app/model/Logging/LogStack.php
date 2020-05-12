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

    /**
     * @param ILogger $child
     */
    public function setChild(ILogger $child) {
        $this->child = $child;
    }

    /**
     * @param Message $message
     */
    public final function log(Message $message) {
        $this->doLog($message);
        if ($this->getChild()) {
            $this->getChild()->log($message);
        }
    }

    /**
     * @param $message
     * @return void
     */
    abstract protected function doLog(Message $message);
}
