<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Components\Forms\Controls\DateInputs\TimeInput;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Models\Transitions\Machine\Machine;
use Nette\Database\Connection;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

/**
 * @phpstan-type MetaItem array{
 *      name:string,
 *      table:string,
 *      nativetype:string,
 *      size:int,
 *      nullable:bool,
 *      default:mixed,
 *      autoincrement:bool,
 *      primary:bool,
 *      vendor:array<string,mixed>,
 * }
 */
class DBReflectionFactory extends AbstractFactory
{

    private Connection $connection;
    /** @phpstan-var array<string,MetaItem> */
    private array $columns;
    private ORMFactory $tableReflectionFactory;

    public function __construct(
        Connection $connection,
        ORMFactory $tableReflectionFactory
    ) {
        $this->connection = $connection;
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    public function createComponent(Field $field): BaseControl
    {
        $element = null;
        try {
            $element = $this->tableReflectionFactory->loadColumnFactory('event_participant', $field->name)->createField(
            );
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
        if ($field->holder->getModelState()->value === Machine::STATE_INIT && $field->getDefault() === null) {
            $column = $this->resolveColumn($field);
            $default = $column['default'];
        } else {
            $default = $field->getValue();
        }
        $control->setDefaultValue($default);
    }

    /**
     * @phpstan-return MetaItem
     */
    private function resolveColumn(Field $field): array
    {
        $column = $this->getColumnMetadata($field->name);
        if ($column === null) {
            throw new InvalidArgumentException("Cannot find reflection for field '$field->name'.");
        }
        return $column;
    }

    /**
     * @phpstan-return MetaItem|null
     */
    private function getColumnMetadata(string $column): ?array
    {
        if (!isset($this->columns)) {
            $this->columns = [];
            foreach ($this->connection->getDriver()->getColumns('event_participant') as $columnMeta) {
                $this->columns[$columnMeta['name']] = $columnMeta;//@phpstan-ignore-line
            }
        }
        return $this->columns[$column] ?? null;
    }
}
