<?php

namespace FKSDB\Components\Forms\Factories\Events;

use AbstractServiceMulti;
use AbstractServiceSingle;
use Events\Machine\BaseMachine;
use Events\Model\Holder\Field;
use FKSDB\Components\Forms\Controls\TimeBox;
use Nette\ComponentModel\Component;
use Nette\Database\Connection;
use Nette\Forms\Container;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DBReflectionFactory extends AbstractFactory {

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array tableName => columnName[]
     */
    private $columns = [];

    /**
     * DBReflectionFactory constructor.
     * @param Connection $connection
     */
    function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     * @return TimeBox|Checkbox|TextArea|TextInput
     */
    protected function createComponent(Field $field, BaseMachine $machine, Container $container) {
        $column = $this->resolveColumn($field);
        $type = $column['nativetype'];
        $size = $column['size'];

        /*
         * Create element
         */
        if ($type == 'TINYINT' && $size == 1) {
            $element = new Checkbox($field->getLabel());
        } else if (substr_compare($type, 'INT', '-3') == 0) {
            $element = new TextInput($field->getLabel());
            $element->addCondition(Form::FILLED)
                    ->addRule(Form::INTEGER, _('%label musí být celé číslo.'))
                    ->addRule(Form::MAX_LENGTH, null, $size);
        } else if ($type == 'TEXT') {
            $element = new TextArea($field->getLabel());
        } else if ($type == 'TIME') {
            $element = new TimeBox($field->getLabel());
        } else {
            $element = new TextInput($field->getLabel());
            if ($size) {
                $element->addRule(Form::MAX_LENGTH, null, $size);
            }
        }
        $element->setOption('description', $field->getDescription());
        return $element;
    }

    /**
     * @param $component
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     */
    protected function setDefaultValue($component, Field $field, BaseMachine $machine, Container $container) {
        if ($machine->getState() == BaseMachine::STATE_INIT && $field->getDefault() === null) {
            $column = $this->resolveColumn($field);
            $default = $column['default'];
        } else {
            $default = $field->getValue();
        }
        $component->setDefaultValue($default);
    }

    /**
     * @param $component
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     */
    protected function setDisabled($component, Field $field, BaseMachine $machine, Container $container) {
        $component->setDisabled();
    }

    /**
     * @param Component $component
     * @return Component|\Nette\Forms\IControl
     */
    public function getMainControl(Component $component) {
        return $component;
    }

    /**
     * @param Field $field
     * @return null
     */
    private function resolveColumn(Field $field) {
        $service = $field->getBaseHolder()->getService();
        $columnName = $field->getName();

        $column = null;
        if ($service instanceof AbstractServiceSingle) {
            $tableName = $service->getTable()->getName();
            $column = $this->getColumnMetadata($tableName, $columnName);
        } else if ($service instanceof AbstractServiceMulti) {
            $tableName = $service->getMainService()->getTable()->getName();
            $column = $this->getColumnMetadata($tableName, $columnName);
            if ($column === null) {
                $tableName = $service->getJoinedService()->getTable()->getName();
                $column = $this->getColumnMetadata($tableName, $columnName);
            }
        }
        if ($column === null) {
            throw new InvalidArgumentException("Cannot find reflection for field '{$field->getName()}'.");
        }
        return $column;
    }

    /**
     * @param $table
     * @param $column
     * @return null
     */
    private function getColumnMetadata($table, $column) {
        if (!isset($this->columns[$table])) {
            $columns = [];
            foreach ($this->connection->getSupplementalDriver()->getColumns($table) as $columnMeta) {
                $columns[$columnMeta['name']] = $columnMeta;
            }
            $this->columns[$table] = $columns;
        }
        if (isset($this->columns[$table][$column])) {
            return $this->columns[$table][$column];
        } else {
            return null;
        }
    }

}

