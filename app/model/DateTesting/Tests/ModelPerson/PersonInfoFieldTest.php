<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\DataTesting\TestLog;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\DataTesting\TestsLogger;
use Nette\Application\BadRequestException;

/**
 * Class PersonInfoFieldTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonInfoFieldTest extends PersonFileLevelTest {
    /**
     * AbstractPersonInfoFieldTest constructor.
     * @param TableReflectionFactory $tableReflectionFactory
     * @param string $factoryFieldName
     * @throws BadRequestException
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory, string $factoryFieldName) {
        parent::__construct($tableReflectionFactory, 'person_info.' . $factoryFieldName);
    }

    /**
     * @param TestsLogger $logger
     * @param ModelPerson $person
     * @return void
     */
    final public function run(TestsLogger $logger, ModelPerson $person) {
        $info = $person->getInfo();
        if (!$info) {
            $logger->log(new TestLog($this->getTitle(), 'Person info is not set', TestLog::LVL_INFO));
        }
        $this->getRowFactory()->runTest($logger, $info);
    }
}
