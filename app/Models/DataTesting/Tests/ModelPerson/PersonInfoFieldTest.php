<?php

namespace FKSDB\Models\DataTesting\Tests\ModelPerson;


use FKSDB\Models\DataTesting\TestLog;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\Logging\ILogger;
use FKSDB\Models\ORM\Models\ModelPerson;


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
