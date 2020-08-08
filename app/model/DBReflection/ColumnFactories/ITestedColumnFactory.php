<?php

namespace FKSDB\DBReflection\ColumnFactories;

use FKSDB\Logging\ILogger;
use FKSDB\ORM\AbstractModelSingle;

/**
 * Interface ITestedRowFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface ITestedColumnFactory extends IColumnFactory {
    public function runTest(ILogger $logger, AbstractModelSingle $model): void;
}
