<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\DataTesting\TestsLogger;

/**
 * Interface ITestedRowFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface ITestedRowFactory {
    /**
     * @param TestsLogger $logger
     * @param AbstractModelSingle $model
     * @return void
     */
    public function runTest(TestsLogger $logger, AbstractModelSingle $model);
}
