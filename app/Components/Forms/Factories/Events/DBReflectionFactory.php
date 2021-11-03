<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Components\Forms\Controls\DateInputs\TimeInput;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\NetteORM\AbstractService;
use FKSDB\Models\ORM\ServicesMulti\AbstractServiceMulti;
use FKSDB\Models\Transitions\Machine\Machine;
use Nette\Database\Connection;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

class DBReflectionFactory extends AbstractFactory {

    private Connection $connection;
    /** @var array tableName => columnName[] */
    private array $columns = [];
    private ORMFactory $tableReflectionFactory;

    public function __construct(Connection $connection, ORMFactory $tableReflectionFactory) {
        $this->connection = $connection;
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    public function createComponent(Field $field): BaseControl {
        $element = null;
        try {
            $service = $field->getBaseHolder()->getService();
            $columnName = $field->getName();

            $service->getTable()->getName();
            $tableName = null;
            if ($service instanceof AbstractService) {
                $tableName = $service->getTable()->getName();
            } elseif ($service instanceof AbstractServiceMulti) {
                $tableName = $service->mainService->getTable()->getName();
            }
            if ($tableName) {
                $element = $this->tableReflectionFactory->loadColumnFactory($tableName, $columnName)->createField();
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
                    ->addRule(Form::INTEGER, _('%label must be an integer.'));
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

    protected function setDefaultValue(BaseControl $control, Field $field): void {
        if ($field->getBaseHolder()->getModelState() == Machine::STATE_INIT && $field->getDefault() === null) {
            $column = $this->resolveColumn($field);
            $default = $column['default'];
        } else {
            $default = $field->getValue();
        }
        $control->setDefaultValue($default);
    }

    private function resolveColumn(Field $field): ?array {
        $service = $field->getBaseHolder()->getService();
        $columnName = $field->getName();

        $column = null;
        if ($service instanceof AbstractService) {
            $tableName = $service->getTable()->getName();
            $column = $this->getColumnMetadata($tableName, $columnName);
        } elseif ($service instanceof AbstractServiceMulti) {
            $tableName = $service->mainService->getTable()->getName();
            $column = $this->getColumnMetadata($tableName, $columnName);
            if ($column === null) {
                $tableName = $service->joinedService->getTable()->getName();
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
            foreach ($this->connection->getDriver()->getColumns($table) as $columnMeta) {
                $columns[$columnMeta['name']] = $columnMeta;
            }
            $this->columns[$table] = $columns;
        }
        return $this->columns[$table][$column] ?? null;
    }
}
