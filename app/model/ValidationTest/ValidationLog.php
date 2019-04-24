<?php


namespace FKSDB\ValidationTest;

use FKSDB\Messages\Message;
use Nette\Utils\Html;

/**
 * Class ValidationLog
 * @package FKSDB\ValidationTest
 */
class ValidationLog extends Message {
    /**
     * @var Html
     */
    public $detail;
    /**
     * @var string
     */
    public $testName;

    /**
     * ValidationLog constructor.
     * @param string $testName
     * @param string $message
     * @param string $level
     * @param Html|null $detail
     */
    public function __construct(string $testName, string $message, string $level, Html $detail = null) {
        parent::__construct($message, $level);
        $this->detail = $detail;
        $this->testName = $testName;
    }
}
