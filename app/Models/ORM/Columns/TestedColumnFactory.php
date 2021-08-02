<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns;

use FKSDB\Models\Logging\Logger;
use Fykosak\NetteORM\AbstractModel;

interface TestedColumnFactory
{

    public function runTest(Logger $logger, AbstractModel $model): void;
}
