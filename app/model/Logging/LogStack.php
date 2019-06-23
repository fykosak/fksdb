<?php

namespace FKSDB\Logging;

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
     * @param $message
     * @param int $level
     */
    public final function log($message, $level = self::INFO) {
        $this->doLog($message, $level);
        if ($this->getChild()) {
            $this->getChild()->log($message, $level);
        }
    }

    /**
     * @param $message
     * @param $level
     * @return mixed
     */
    abstract protected function doLog($message, $level);
}
