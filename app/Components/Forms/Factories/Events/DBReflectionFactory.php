<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Components\Forms\Controls\DateInputs\TimeInput;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Machine\Machine;
use Nette\Database\Connection;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

class DBReflectionFactory extends AbstractFactory
{

    private Connection $connection;
    /** @var array tableName => columnName[] */
    private array $columns = [];
    private ORMFactory $tableReflectionFactory;
    private EventParticipantService $eventParticipantService;

    public function __construct(
        Connection $connection,
        ORMFactory $tableReflectionFactory,
        EventParticipantService $eventParticipantService
    ) {
        $this->connection = $connection;
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->eventParticipantService = $eventParticipantService;
    }

    public function createComponent(Field $field): BaseControl
    {
        $element = null;
        try {
            $tableName = $this->eventParticipantService->getTable()->getName();
            $element = $this->tableReflectionFactory->loadColumnFactory($tableName, $field->name)->createField();
        } catch (\Throwable $e) {
        }
        $column = $this->resolveColumn($field);
        $type = $column['nativetype'];
        $size = $column['size'];

        /*
         * Create element
         */
        if (!$element) {
            if ($type == 'TINYINT' && $size == 1) {
                $element = new Checkbox($field->label);
            } elseif (substr_compare($type, 'INT', -3) == 0) {
                $element = new TextInput($field->label);
                $element->addCondition(Form::FILLED)
                    ->addRule(Form::INTEGER, _('%label must be an integer.'));
                if ($size) {
                    $element->addRule(Form::MAX_LENGTH, _('Max length reached'), $size);
                }
            } elseif ($type == 'TEXT') {
                $element = new TextArea($field->label);
            } elseif ($type == 'TIME') {
                $element = new TimeInput($field->label);
            } else {
                $element = new TextInput($field->label);
                if ($size) {
                    $element->addRule(Form::MAX_LENGTH, _('Max length reached'), $size);
                }
            }
        }
        $element->caption = $field->label;
        if ($field->description) {
            $element->setOption('description', $field->description);
        }

        return $element;
    }

    protected function setDefaultValue(BaseControl $control, Field $field): void
    {
        if ($field->holder->getModelState() == Machine::STATE_INIT && $field->getDefault() === null) {
            $column = $this->resolveColumn($field);
            $default = $column['default'];
        } else {
            $default = $field->getValue();
        }
        $control->setDefaultValue($default);
    }

    private function resolveColumn(Field $field): ?array
    {
        $tableName = $this->eventParticipantService->getTable()->getName();
        $column = $this->getColumnMetadata($tableName, $field->name);

        if ($column === null) {
            throw new InvalidArgumentException("Cannot find reflection for field '$field->name'.");
        }
        return $column;
    }

    private function getColumnMetadata(string $table, string $column): ?array
    {
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
