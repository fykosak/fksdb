<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\DataTesting\TestLog;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\DataTesting\TestsLogger;
use Nette\Application\BadRequestException;

/**
 * Class PersonInfoFieldTest
 * @package FKSDB\DataTesting\Tests\Person
 */
class PersonInfoFieldTest extends PersonFileLevelTest {
    /**
     * AbstractPersonInfoFieldTest constructor.
     * @param TableReflectionFactory $tableReflectionFactory
     * @param string $factoryFieldName
     * @throws BadRequestException
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory, string $factoryFieldName) {
        parent::__construct($tableReflectionFactory, DbNames::TAB_PERSON_INFO, $factoryFieldName);
    }

    /**
     * @param TestsLogger $logger
     * @param ModelPerson $person
     * @return TestLog
     */
    final public function run(TestsLogger $logger, ModelPerson $person) {
        $info = $person->getInfo();
        if (!$info) {
            $logger->log(new TestLog($this->getTitle(), 'Person info is not set', TestLog::LVL_INFO));
        }
        return $this->getRowFactory()->runTest($logger, $info);
    }
}
