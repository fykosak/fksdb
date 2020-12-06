<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Components\Forms\Controls\DateInputs\TimeInput;
use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Model\Holder\Field;
use FKSDB\DBReflection\DBReflectionFactory as ReflectionFactory;
use FKSDB\ORM\Services\AbstractServiceSingle;
use FKSDB\ORM\ServicesMulti\AbstractServiceMulti;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IComponent;
use Nette\Database\Connection;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DBReflectionFactory extends AbstractFactory {

    private Connection $connection;

    /** @var array tableName => columnName[] */
    private $columns = [];

    private ReflectionFactory $tableReflectionFactory;

    public function __construct(Connection $connection, ReflectionFactory $tableReflectionFactory) {
        $this->connection = $connection;
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    public function createComponent(Field $field): IComponent {
        $element = null;
        try {
            $service = $field->getBaseHolder()->getService();
            $columnName = $field->getName();

            $service->getTable()->getName();
            $tableName = null;
            if ($service instanceof AbstractServiceSingle) {
                $tableName = $service->getTable()->getName();
            } elseif ($service instanceof AbstractServiceMulti) {
                $tableName = $service->getMainService()->getTable()->getName();
            }
            if ($tableName) {
                $element = $this->tableReflectionFactory->loadColumnFactory($tableName . '.' . $columnName)->createField();
            }
        } catch (\Exception $e) {
        }
        $column = $this->resolveColumn($field);
        $type = $column['nativetype'];
        $size = $column['size'];

        /*
         * Create element
         */
        if (!$element) {
            if ($type == 'TINYINT' && $size == 1) {
                $element = new Checkbox($field->getLabel());
            } elseif (substr_compare($type, 'INT', '-3') == 0) {
                $element = new TextInput($field->getLabel());
                $element->addCondition(Form::FILLED)
                    ->addRule(Form::INTEGER, _('%label musí být celé číslo.'));
                if ($size) {
                    $element->addRule(Form::MAX_LENGTH, null, $size);
                }
            } elseif ($type == 'TEXT') {
                $element = new TextArea($field->getLabel());
            } elseif ($type == 'TIME') {
                $element = new TimeInput($field->getLabel());
            } else {
                $element = new TextInput($field->getLabel());
                if ($size) {
                    $element->addRule(Form::MAX_LENGTH, null, $size);
                }
            }
        }
        $element->caption = $field->getLabel();
        if ($field->getDescription()) {

            $element->setOption('description', $field->getDescription());
        }

        return $element;
    }

    /**
     * @param IComponent|BaseControl $component
     * @param Field $field
     * @return void
     */
    protected function setDefaultValue(IComponent $component, Field $field): void {

        if ($field->getBaseHolder()->getModelState() == BaseMachine::STATE_INIT && $field->getDefault() === null) {
            $column = $this->resolveColumn($field);
            $default = $column['default'];
        } else {
            $default = $field->getValue();
        }
        $component->setDefaultValue($default);
    }

    /**
     * @param IComponent|BaseControl $component
     * @return void
     */
    protected function setDisabled(IComponent $component): void {
        $component->setDisabled();
    }

    /**
     * @param Component|IComponent $component
     * @return Component|IControl
     */
    public function getMainControl(IComponent $component): IControl {
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
        } elseif ($service instanceof AbstractServiceMulti) {
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

    private function getColumnMetadata(string $table, string $column): ?array {
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
