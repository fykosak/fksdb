<?php


namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Models\Events\Machine\BaseMachine;
use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use FKSDB\Models\ORM\ServicesMulti\AbstractServiceMulti;
use Nette\Database\Explorer;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Utils\Html;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @deprecated use person_schedule UC
 */
class MultiResourceAvailability extends AbstractAdjustment {

    /** @var array fields that specifies amount used (string masks) */
    private array $fields;

    /** @var string|array Name of event parameter that hold overall capacity. */
    private $paramCapacity;
    /** @var array|string */
    private $includeStates;
    /** @var string[] */
    private array $excludeStates;
    private string $message;

    private Explorer $database;

    /**
     *
     * @param array|string $fields Fields that contain amount of the resource
     * @param string|array $paramCapacity Name of the parameter with overall capacity.
     * @param string $message String '%avail' will be substitued for the actual amount of available resource.
     * @param Explorer $explorer
     * @param string|array $includeStates any state or array of state
     * @param string|array $excludeStates any state or array of state
     */
    public function __construct(array $fields, $paramCapacity, string $message, Explorer $explorer, $includeStates = \FKSDB\Models\Transitions\Machine\Machine::STATE_ANY, array $excludeStates = ['cancelled']) {
        $this->fields = $fields;
        $this->database = $explorer;
        $this->paramCapacity = $paramCapacity;
        $this->message = $message;
        $this->includeStates = $includeStates;
        $this->excludeStates = $excludeStates;
    }

    protected function innerAdjust(Form $form, Machine $machine, Holder $holder): void {
        $groups = $holder->getGroupedSecondaryHolders();
        $groups[] = [
            'service' => $holder->getPrimaryHolder()->getService(),
            'holders' => [$holder->getPrimaryHolder()],
        ];
        /** @var BaseHolder[][]|Field[][]|AbstractServiceSingle[]|AbstractServiceMulti[] $services */
        $services = [];
        $controls = [];
        foreach ($groups as $group) {
            $holders = [];
            $field = null;
            /** @var BaseHolder $baseHolder */
            foreach ($group['holders'] as $baseHolder) {
                $name = $baseHolder->getName();
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
                $services[] = [
                    'service' => $group['service'],
                    'holders' => $holders,
                    'field' => $field,
                ];
            }
        }

        $usage = [];
        foreach ($services as $serviceData) {
            /** @var BaseHolder $firstHolder */
            $firstHolder = reset($serviceData['holders']);
            $event = $firstHolder->getEvent();
            $tableName = $serviceData['service']->getTable()->getName();
            $table = $this->database->table($tableName);

            $table->where($firstHolder->getEventIdColumn(), $event->getPrimary());
            if ($this->includeStates !== \FKSDB\Models\Transitions\Machine\Machine::STATE_ANY) {
                $table->where(BaseHolder::STATE_COLUMN, $this->includeStates);
            }
            if ($this->excludeStates !== \FKSDB\Models\Transitions\Machine\Machine::STATE_ANY) {
                $table->where('NOT ' . BaseHolder::STATE_COLUMN, $this->excludeStates);
            } else {
                $table->where('1=0');
            }

            $primaries = array_map(function (BaseHolder $baseHolder) {
                return $baseHolder->getModel()->getPrimary(false);
            }, $serviceData['holders']);
            $primaries = array_filter($primaries, function ($primary): bool {
                return (bool)$primary;
            });

            $column = BaseHolder::getBareColumn($serviceData['field']);
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
            /** @var IControl $control */
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
