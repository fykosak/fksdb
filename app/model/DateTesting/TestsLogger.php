<?php

namespace FKSDB\DataTesting;

/**
 * Class TestsLogger
 * @package FKSDB\DataTesting
 */
class TestsLogger {
    /**
     * @var TestLog[]
     */
    private $logs = [];

    /**
     * @inheritDoc
     */
    public function log(TestLog $log) {
        $this->logs[] = $log;
    }

    /**
     * @return array
     */
    public function getLogs(): array {
        return $this->logs;
    }
}
