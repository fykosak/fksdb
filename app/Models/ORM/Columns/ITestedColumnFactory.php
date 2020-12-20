<?php

namespace FKSDB\Models\ORM\Columns;

use FKSDB\Models\Logging\ILogger;
use FKSDB\Models\ORM\Models\AbstractModelSingle;

/**
 * Interface ITestedRowFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface ITestedColumnFactory extends IColumnFactory {
    public function runTest(ILogger $logger, AbstractModelSingle $model): void;
}
