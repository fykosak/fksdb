<?php

namespace FKSDB\Model\DBReflection\ColumnFactories;

use FKSDB\Model\Logging\ILogger;
use FKSDB\Model\ORM\Models\AbstractModelSingle;

/**
 * Interface ITestedRowFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface ITestedColumnFactory extends IColumnFactory {
    public function runTest(ILogger $logger, AbstractModelSingle $model): void;
}
