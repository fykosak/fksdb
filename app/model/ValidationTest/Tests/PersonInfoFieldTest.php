<?php


namespace FKSDB\ValidationTest;


use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\BadRequestException;

/**
 * Class AbstractPersonInfoFieldTest
 * @package FKSDB\ValidationTest
 */
class PersonInfoFieldTest extends AbstractFieldLevelTest {
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
     * @param ModelPerson $person
     * @return ValidationLog
     */
    public final function run(ModelPerson $person): ValidationLog {
        $info = $person->getInfo();
        if (!$info) {
            return new ValidationLog($this->getTitle(), 'Person info is not set', ValidationLog::LVL_INFO);
        }
        return $this->getRowFactory()->runTest($info);
    }
}
