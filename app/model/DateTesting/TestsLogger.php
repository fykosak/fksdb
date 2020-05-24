<?php

namespace FKSDB\DataTesting;

/**
 * Class TestsLogger
 * *
 */
class TestsLogger {
    /**
     * @var TestLog[]
     */
    private $logs = [];

    /**
     * @param TestLog $log
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
