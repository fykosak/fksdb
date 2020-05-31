<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Exceptions\BadTypeException;
use Nette\Forms\Controls\BaseControl;

/**
 * Class SingleReflectionFactory
 * @author Michal Červeňák <miso@fykos.cz>
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

    abstract protected function getTableName(): string;

    /**
     * @param string $fieldName
     * @return AbstractRow
     * @throws BadTypeException
     */
    protected function loadFactory(string $fieldName): AbstractRow {
        return $this->tableReflectionFactory->loadRowFactory($this->getTableName() . '.' . $fieldName);
    }

    /**
     * @param string $fieldName
     * @param array $args
     * @return BaseControl
     * @throws \Exception
     */
    public function createField(string $fieldName, ...$args): BaseControl {
        return $this->loadFactory($fieldName)->createField(...$args);
    }

    /**
     * @param array $fields
     * @return ModelContainer
     * @throws \Exception
     */
    public function createContainer(array $fields): ModelContainer {
        $container = new ModelContainer();

        foreach ($fields as $field) {
            $control = $this->createField($field);
            $container->addComponent($control, $field);
        }
        return $container;
    }
}
