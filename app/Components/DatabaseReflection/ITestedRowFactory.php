<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\DataTesting\TestsLogger;
use FKSDB\DataTesting\TestLog;

/**
 * Interface ITestedRowFactory
 * @package FKSDB\Components\Forms\Factories
 */
interface ITestedRowFactory {
    /**
     * @param TestsLogger $logger
     * @param AbstractModelSingle $model
     * @return TestLog
     */
    public function runTest(TestsLogger $logger, AbstractModelSingle $model);
}
