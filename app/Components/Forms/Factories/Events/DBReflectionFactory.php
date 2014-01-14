<?php

namespace FKSDB\Components\Forms\Factories\Events;

use AbstractServiceMulti;
use AbstractServiceSingle;
use Events\Machine\BaseMachine;
use Events\Model\Field;
use Events\Model\Field as Field2;
use Nette\Application\UI\Form;
use Nette\ComponentModel\Component;
use Nette\Database\Connection;
use Nette\Forms\Container;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\TextInput;
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
    private $columns = array();

    function __construct(Connection $connection) {
        $this->connection = $connection;
    }

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
        } else {
            $element = new TextInput($field->getLabel());
            if ($size) {
                $element->addRule(Form::MAX_LENGTH, null, $size);
            }
        }
        return $element;
    }

    protected function setDefaultValue($component, Field $field, BaseMachine $machine, Container $container) {
        $component->setDefaultValue($field->getValue());
    }

    protected function setDisabled($component, Field $field, BaseMachine $machine, Container $container) {
        $component->setDisabled();
    }

    public function getMainControl(Component $component) {
        return $component;
    }

    private function resolveColumn(Field2 $field) {
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

    private function getColumnMetadata($table, $column) {
        if (!isset($this->columns[$table])) {
            $columns = array();
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

