<?php

namespace FKSDB\Tests\ModelsTests\Export\Formats;

use FKSDB\StoredQuery\StoredQueryParameter;

class MockQueryParameter extends StoredQueryParameter {
    public function __construct(string $name) {
        parent::__construct($name, null, \PDO::PARAM_STR);
    }
}
