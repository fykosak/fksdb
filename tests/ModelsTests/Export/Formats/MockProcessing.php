<?php

namespace FKSDB\Tests\ModelsTests\Export\Formats;

use FKSDB\Model\StoredQuery\StoredQueryPostProcessing;

class MockProcessing extends StoredQueryPostProcessing {

    public function getMaxPoints(): int {
        return 0;
    }

    public function getDescription(): string {
        return '';
    }

    public function processData(\PDOStatement $data): \PDOStatement {
        return $data;
    }
}
