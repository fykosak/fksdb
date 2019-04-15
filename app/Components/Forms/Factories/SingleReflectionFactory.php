<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\Org\SinceRow;
use FKSDB\Components\DatabaseReflection\PersonHistory\StudyYearRow;
use Nette\Forms\Controls\BaseControl;

/**
 * Class SingleReflectionFactory
 * @package FKSDB\Components\Forms\Factories
 */
abstract class SingleReflectionFactory {
    /**
     * @var TableReflectionFactory
     */
    protected $tableReflectionFactory;

    /**
     * PersonHistoryFactory constructor.
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory) {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @return string
     */
    abstract protected function getTableName(): string;

    /**
     * @param string $fieldName
     * @return AbstractRow|SinceRow|StudyYearRow
     * @throws \Exception
     */
    protected function loadFactory(string $fieldName): AbstractRow {
        return $this->tableReflectionFactory->loadService($this->getTableName(), $fieldName);
    }

    /**
     * @param string $fieldName
     * @return BaseControl
     * @throws \Exception
     */
    public function createField(string $fieldName): BaseControl {
        return $this->loadFactory($fieldName)->createField();
    }
}
