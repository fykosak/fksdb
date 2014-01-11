<?php

namespace FKSDB\Components\Forms\Factories\Events;

use AbstractServiceMulti;
use AbstractServiceSingle;
use Events\Machine\BaseMachine;
use Events\Model\Field;
use Nette\Database\Connection;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class DBReflectionFactory implements IFieldFactory {

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

    public function create(Field $field, BaseMachine $machine) {
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
            $element->addRule(Form::INTEGER, _('%label musí být celé číslo.'))
                    ->addRule(Form::MAX_LENGTH, null, $size);
        } else {
            $element = new TextInput($field->getLabel());
            if ($size) {
                $element->addRule(Form::MAX_LENGTH, null, $size);
            }
        }

        /*
         * Set attributes
         */
        $element->setDisabled(!$field->isModifiable($machine));
        if ($field->isRequired($machine)) {
            $element->setRequired(_('%label je povinná položka.'));
        }

        /*
         * Set value
         */
        $element->setDefaultValue($field->getValue());

        return $element;
    }

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
            throw new InvalidArgumentException("Cannot find reflection for field '$name'.");
        }
        return $column;
    }

    private function getColumnMetadata($table, $column) {
        if (!isset($this->columns[$table])) {
            $columns = array();
            foreach ($this->connection->getSupplementalDriver()->getColumns($table) as $column) {
                $columns[$column['name']] = $column;
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
