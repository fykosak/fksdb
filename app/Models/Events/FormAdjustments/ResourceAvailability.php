<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\ORM\ServicesMulti\ServiceMulti;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
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
        array $includeStates = [AbstractMachine::STATE_ANY],
        array $excludeStates = ['cancelled']
    ) {
        $this->fields = $fields;
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

        $usage = 0;
        /** @var Service|ServiceMulti[]|BaseHolder[][] $dataService */
        foreach ($sService as $dataService) {
            /** @var BaseHolder $firstHolder */
            $firstHolder = reset($dataService['holders']);
            $event = $firstHolder->event;
            /** @var TypedGroupedSelection $table */
            $table = $dataService['service']->getTable();
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
