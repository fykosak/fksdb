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
     * @return void
     */
    public function log(TestLog $log) {
        $this->logs[] = $log;
    }

    /**
     * @return TestLog[]
     */
    public function getLogs(): array {
        return $this->logs;
    }
}
