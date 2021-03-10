<?php

namespace FKSDB\Models\ORM\Columns;

use FKSDB\Models\Logging\Logger;
use Fykosak\NetteORM\AbstractModel;

/**
 * Interface ITestedRowFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface TestedColumnFactory {

    public function runTest(Logger $logger, AbstractModel $model): void;
}
