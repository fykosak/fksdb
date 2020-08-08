<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\DataTesting\TestLog;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class PersonInfoFieldTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonInfoFieldTest extends PersonFileLevelTest {

    /**
     * @param ILogger $logger
     * @param ModelPerson $person
     * @return void
     * @throws BadTypeException
     */
    final public function run(ILogger $logger, ModelPerson $person) {
        $info = $person->getInfo();
        if (!$info) {
            $logger->log(new TestLog($this->getTitle(), 'Person info is not set', TestLog::LVL_INFO));
        }
        $this->getRowFactory()->runTest($logger, $info);
    }
}
