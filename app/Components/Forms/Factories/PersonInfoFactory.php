<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\ORM\DbNames;
use Nette\Forms\IControl;

/**
 * Class PersonHistoryFactory
 * @package FKSDB\Components\Forms\Factories
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonInfoFactory {
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;

    /**
     * PersonInfoFactory constructor.
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory) {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_PERSON_INFO;
    }

    /**
     * @param string $fieldName
     * @return IControl
     * @throws \Exception
     */
    public function createField(string $fieldName): IControl {
        return $this->tableReflectionFactory->loadService($this->getTableName(), $fieldName)->createField();
    }
}
