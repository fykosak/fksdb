<?php


namespace FKSDB\ValidationTest;

/**
 * Class ValidationLog
 * @package FKSDB\ValidationTest
 */
class ValidationLog {
    /**
     * @var string
     */
    public $level;
    /**
     * @var string
     */
    public $message;

    /**
     * ValidationLog constructor.
     * @param string $level
     * @param string $message
     */
    public function __construct(string $level, string $message) {
        $this->level = $level;
        $this->message = $message;
    }
}
