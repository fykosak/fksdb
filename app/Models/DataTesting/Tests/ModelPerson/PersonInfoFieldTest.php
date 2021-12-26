<?php

namespace FKSDB\Models\DataTesting\Tests\ModelPerson;

use FKSDB\Models\DataTesting\TestLog;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\Logging\Logger;
use FKSDB\Models\ORM\Models\ModelPerson;
use Fykosak\Utils\Logging\MessageLevel;

class PersonInfoFieldTest extends PersonFileLevelTest {

    /**
     * @throws BadTypeException
     */
    final public function run(Logger $logger, ModelPerson $person): void {
        $info = $person->getInfo();
        if (!$info) {
            $logger->log(new TestLog($this->title, 'Person info is not set', MessageLevel::INFO));
            return;
        }
        $this->getRowFactory()->runTest($logger, $info);
    }
}
