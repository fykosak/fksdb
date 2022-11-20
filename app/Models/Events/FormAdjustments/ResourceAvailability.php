<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Forms\Form;
use Nette\Forms\Control;

/**
 * @deprecated use person_schedule UC
 */
class ResourceAvailability extends AbstractAdjustment
{

    /** @var array fields that specifies amount used (string masks) */
    private array $fields;
    /** @var string Name of event parameter that hold overall capacity. */
    private string $paramCapacity;
    private array $includeStates;
    /** @var string[] */
    private array $excludeStates;
    private string $message;

    /**
     *
     * @param array $fields Fields that contain amount of the resource
     * @param string $paramCapacity Name of the parameter with overall capacity.
     * @param string $message String '%avail' will be substitued for the actual amount of available resource.
     * @param array $includeStates any state or array of state
     * @param array $excludeStates any state or array of state
     */
    public function __construct(
        array $fields,
        string $paramCapacity,
        string $message,
        array $includeStates = [Machine::STATE_ANY],
        array $excludeStates = ['cancelled']
    ) {
        $this->fields = $fields;
        $this->paramCapacity = $paramCapacity;
        $this->message = $message;
        $this->includeStates = $includeStates;
        $this->excludeStates = $excludeStates;
    }

    /**
     * @param BaseHolder $holder
     */
    protected function innerAdjust(Form $form, ModelHolder $holder): void
    {
        $sService = [];
        $controls = [];

        $holders = [];
        $field = null;
        $name = $holder->name;
        foreach ($this->fields as $fieldMask) {
            $foundControls = $this->getControl($fieldMask);
            if (!$foundControls) {
                continue;
            }
            if (isset($foundControls[$name])) {
                $holders[] = $holder;
                $controls[] = $foundControls[$name];
                $field = $fieldMask;
            } elseif ($name == substr($fieldMask, 0, strpos($fieldMask, self::DELIMITER))) {
                $holders[] = $holder;
                $controls[] = reset($foundControls); // assume single result;
                $field = $fieldMask;
            }
        }

        if ($holders) {
            $sService[] = [
                'service' => $holder->service,
                'field' => $field,
            ];
        }

        $usage = 0;
        /** @var Service|BaseHolder[][] $dataService */
        foreach ($sService as $dataService) {
            $event = $holder->event;
            /** @var TypedGroupedSelection $table */
            $table = $dataService['service']->getTable();
            $table->where('event_participant.event_id', $event->getPrimary());
            if (!in_array(Machine::STATE_ANY, $this->includeStates)) {
                $table->where('status', $this->includeStates);
            }
            if (!in_array(Machine::STATE_ANY, $this->excludeStates)) {
                $table->where('NOT ' . 'status', $this->excludeStates);
            } else {
                $table->where('1=0');
            }
            $model = $holder->getModel();

            $column = BaseHolder::getBareColumn($dataService['field']);
            $pk = $table->getName() . '.' . $table->getPrimary();
            if ($model) {
                $table->where("NOT $pk IN", [$model->getPrimary()]);
            }
            $usage += $table->sum($column);
        }

        $capacity = $holder->getParameter($this->paramCapacity);

        if ($capacity <= $usage) {
            foreach ($controls as $control) {
                $control->setDisabled();
            }
        }

        $form->onValidate[] = function (Form $form) use ($capacity, $usage, $controls) {
            $controlsUsage = 0;
            /** @var Control $control */
            foreach ($controls as $control) {
                $controlsUsage += (int)$control->getValue();
            }
            if ($capacity < $usage + $controlsUsage) {
                $message = str_replace('%avail', $capacity - $usage, $this->message);
                $form->addError($message);
            }
        };
    }
}
