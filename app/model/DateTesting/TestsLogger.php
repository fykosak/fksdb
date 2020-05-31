<?php

namespace FKSDB\DataTesting;

/**
 * Class TestsLogger
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TestsLogger {
    /**
     * @var TestLog[]
     */
    private array $logs = [];

    public function log(TestLog $log): void {
        $this->logs[] = $log;
    }

    /**
     * @return TestLog[]
     */
    public function getLogs(): array {
        return $this->logs;
    }
}
