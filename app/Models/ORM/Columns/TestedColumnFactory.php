<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns;

use FKSDB\Components\DataTest\TestLogger;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Logger;

interface TestedColumnFactory
{
    public function runTest(TestLogger $logger, Model $model): void;
}
