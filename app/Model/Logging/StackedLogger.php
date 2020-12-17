<?php

namespace FKSDB\Model\Logging;

use FKSDB\Model\Messages\Message;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class StackedLogger implements ILogger {

    private ?ILogger $child = null;

    public function getChild(): ?ILogger {
        return $this->child;
    }

    public function setChild(ILogger $child): void {
        $this->child = $child;
    }

    final public function log(Message $message): void {
        $this->doLog($message);
        if ($this->getChild()) {
            $this->getChild()->log($message);
        }
    }

    abstract protected function doLog(Message $message): void;
}
