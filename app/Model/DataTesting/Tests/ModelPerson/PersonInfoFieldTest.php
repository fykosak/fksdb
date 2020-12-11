<?php

namespace FKSDB\Model\DataTesting\Tests\ModelPerson;

use FKSDB\Model\DataTesting\TestLog;
use FKSDB\Model\Exceptions\BadTypeException;
use Fykosak\Utils\Logging\ILogger;
use FKSDB\Model\ORM\Models\ModelPerson;

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
    final public function run(ILogger $logger, ModelPerson $person): void {
        $info = $person->getInfo();
        if (!$info) {
            $logger->log(new TestLog($this->title, 'Person info is not set', TestLog::LVL_INFO));
            return;
        }
        $this->getRowFactory()->runTest($logger, $info);
    }
}
