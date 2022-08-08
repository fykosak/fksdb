<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
use Fykosak\NetteORM\Service;
use FKSDB\Models\ORM\ServicesMulti\ServiceMulti;
use Nette\Database\Explorer;
use Nette\Forms\Form;
use Nette\Forms\Control;
use Nette\Utils\Html;

/**
 * @deprecated use person_schedule UC
 */
class MultiResourceAvailability extends AbstractAdjustment
{

    /** @var array fields that specifies amount used (string masks) */
    private array $fields;

    /** @var string|array Name of event parameter that hold overall capacity. */
    private $paramCapacity;
    /** @var array|string */
    private array $includeStates;
    /** @var string[] */
    private array $excludeStates;
    private string $message;

    private Explorer $database;

    /**
     *
     * @param array $fields Fields that contain amount of the resource
     * @param array|string $paramCapacity Name of the parameter with overall capacity.
     * @param string $message String '%avail' will be substitued for the actual amount of available resource.
     * @param array $includeStates any state or array of state
     * @param array $excludeStates any state or array of state
     */
    public function __construct(
        array $fields,
        $paramCapacity,
        string $message,
        Explorer $explorer,
        array $includeStates = [AbstractMachine::STATE_ANY],
        array $excludeStates = ['cancelled']
    ) {
        $this->fields = $fields;
        $this->database = $explorer;
        $this->paramCapacity = $paramCapacity;
        $this->message = $message;
        $this->includeStates = $includeStates;
        $this->excludeStates = $excludeStates;
    }

    protected function innerAdjust(Form $form, Holder $holder): void
    {
        $groups = $holder->getGroupedSecondaryHolders();
        $groups[] = [
            'service' => $holder->primaryHolder->getService(),
            'holders' => [$holder->primaryHolder],
        ];
        /** @var BaseHolder[][]|Field[][]|Service[]|ServiceMulti[] $sService */
        $sService = [];
        $controls = [];
        foreach ($groups as $group) {
            $holders = [];
            $field = null;
            /** @var BaseHolder $baseHolder */
            foreach ($group['holders'] as $baseHolder) {
                $name = $baseHolder->name;
                foreach ($this->fields as $fieldMask) {
                    $foundControls = $this->getControl($fieldMask);
                    if (!$foundControls) {
                        continue;
                    }
                    if (isset($foundControls[$name])) {
                        $holders[] = $baseHolder;
                        $controls[] = $foundControls[$name];
                        $field = $fieldMask;
                    } elseif ($name == substr($fieldMask, 0, strpos($fieldMask, self::DELIMITER))) {
                        $holders[] = $baseHolder;
                        $controls[] = reset($foundControls); // assume single result;
                        $field = $fieldMask;
                    }
                }
            }
            if ($holders) {
                $sService[] = [
                    'service' => $group['service'],
                    'holders' => $holders,
                    'field' => $field,
                ];
            }
        }

        $usage = [];
        foreach ($sService as $dataService) {
            /** @var BaseHolder $firstHolder */
            $firstHolder = reset($dataService['holders']);
            $event = $firstHolder->event;
            $tableName = $dataService['service']->getTable()->getName();
            $table = $this->database->table($tableName);

            $table->where($firstHolder->eventIdColumn, $event->getPrimary());
            if (!in_array(AbstractMachine::STATE_ANY, $this->includeStates)) {
                $table->where(BaseHolder::STATE_COLUMN, $this->includeStates);
            }
            if (!in_array(AbstractMachine::STATE_ANY, $this->excludeStates)) {
                $table->where('NOT ' . BaseHolder::STATE_COLUMN, $this->excludeStates);
            } else {
                $table->where('1=0');
            }

            $primaries = array_map(function (BaseHolder $baseHolder) {
                $model = $baseHolder->getModel2();
                return $model ? $model->getPrimary(false) : null;
            }, $dataService['holders']);
            $primaries = array_filter($primaries, fn($primary): bool => (bool)$primary);

            $column = BaseHolder::getBareColumn($dataService['field']);
            $pk = $table->getName() . '.' . $table->getPrimary();
            if ($primaries) {
                $table->where("NOT $pk IN", $primaries);
            }
            $r = $table->select('count(' . $column . ') AS count, ' . $column)->group($column);

            foreach ($r as $row) {
                $k = $row->{$column};
                if (is_numeric($k) && $k > 0) {
                    $usage[$k] = array_key_exists($k, $usage) ? ($usage[$k] + $row->count) : $row->count;
                }
            }
            //$usage += $table->sum($column);
        }
        $capacities = [];
        $o = is_scalar($this->paramCapacity) ? $holder->getParameter($this->paramCapacity) : $this->paramCapacity;
        foreach ($o as $key => $option) {
            if (is_array($option)) {
                $capacities[$option['value']] = $option['capacity'];
            }
        }

        foreach ($controls as $control) {
            $newItems = [];
            $items = $control->getItems();
            foreach ($items as $key => $item) {
                $delta = $capacities[$key] - (array_key_exists($key, $usage) ? $usage[$key] : 0);
                if ($delta > 0) {
                    $newItems[$key] = Html::el('option')->setText($item . '(' . $delta . ')');
                } else {
                    $newItems[$key] = Html::el('option')->setText($item)->addAttributes(['disabled' => true]);
                }
            }
            $control->setItems($newItems);
        }

        $form->onValidate[] = function (Form $form) use ($capacities, $usage, $controls) {
            $controlsUsages = [];
            /** @var Control $control */
            foreach ($controls as $control) {
                $k = $control->getValue();
                /** kontrola ak je k null nieje zaujem o ubytovanie*/
                if ($k) {
                    $controlsUsages[$k] = array_key_exists($k, $controlsUsages) ? ($controlsUsages[$k] + 1) : 1;
                }
            }
            foreach ($controlsUsages as $k => $u) {
                $us = (array_key_exists($k, $usage) ? $usage[$k] : 0) + $u;
                if ($capacities[$k] - $us < 0) {
                    $message = str_replace('%avail', $capacities[$k] - $us, $this->message);
                    $form->addError($message);
                }
            }
        };
    }
}
