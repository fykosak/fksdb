<?php

namespace FKSDB\Components\DatabaseReflection\ColumnFactories;

use FKSDB\Logging\ILogger;
use FKSDB\ORM\AbstractModelSingle;

/**
 * Interface ITestedRowFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface ITestedColumnFactory extends IColumnFactory {
    /**
     * @param ILogger $logger
     * @param AbstractModelSingle $model
     * @return void
     */
    public function runTest(ILogger $logger, AbstractModelSingle $model);
}
