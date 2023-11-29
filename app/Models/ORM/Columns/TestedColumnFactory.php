<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns;

use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Logger;

interface TestedColumnFactory
{
    public function runTest(Logger $logger, Model $model): void;
}
