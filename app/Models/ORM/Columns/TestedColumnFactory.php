<?php

namespace FKSDB\Models\ORM\Columns;

use FKSDB\Models\Logging\Logger;
use FKSDB\Models\ORM\Models\AbstractModelSingle;

/**
 * Interface ITestedRowFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface TestedColumnFactory {

    public function runTest(Logger $logger, AbstractModelSingle $model): void;
}
