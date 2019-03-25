<?php


namespace FKSDB\ValidationTest;

use Nette\Utils\Html;

/**
 * Class ValidationLog
 * @package FKSDB\ValidationTest
 */
class ValidationLog {
    const LVL_DANGER = 'danger';
    const LVL_SUCCESS = 'success';
    const LVL_WARNING = 'warning';
    const LVL_INFO = 'info';
    /**
     * @var string
     */
    public $level;
    /**
     * @var string
     */
    public $message;
    /**
     * @var Html
     */
    public $detail;
    public $testName;

    /**
     * ValidationLog constructor.
     * @param string $testName
     * @param string $message
     * @param string $level
     * @param Html|null $detail
     */
    public function __construct(string $testName, string $message, string $level, Html $detail = null) {
        $this->level = $level;
        $this->message = $message;
        $this->detail = $detail;
        $this->testName = $testName;
    }
}
