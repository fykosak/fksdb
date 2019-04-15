<?php

namespace FKSDB\Components\Forms\Factories;

use Closure;
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
     * @param string $field
     * @return Closure
     * @throws \Exception
     */
    protected function getFieldCallback(string $field): Closure {
        return $this->tableReflectionFactory->createFieldCallback($this->getTableName(), $field);
    }

    /**
     * @param string $fieldName
     * @return BaseControl
     * @throws \Exception
     */
    public function createField(string $fieldName): BaseControl {
        return $this->getFieldCallback($fieldName)();
    }
}
