<?php

namespace Events\FormAdjustments;

use Events\Machine\Machine;
use Events\Model\Holder\BaseHolder;
use Events\Model\Holder\Holder;
use Nette\Forms\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ResourceAvailability extends AbstractAdjustment {

    /**
     * @var array[] fields that specifies amount used (string masks)
     */
    private $fields;

    /**
     * @var string Name of event parameter that hold overall capacity.
     */
    private $paramCapacity;
    private $message;

    private function setFields($fields) {
        if (!is_array($fields)) {
            $fields = array($fields);
        }
        $this->fields = $fields;
    }

    /**
     * 
     * @param array|string $fields Fields that contain amount of the resource
     * @param string $paramCapacity Name of the parameter with overall capacity.
     * @param string $message String '%avail' will be substitued for the actual amount of available resource.
     */
    function __construct($fields, $paramCapacity, $message) {
        $this->setFields($fields);
        $this->paramCapacity = $paramCapacity;
        $this->message = $message;
    }

    protected function _adjust(Form $form, Machine $machine, Holder $holder) {
        $groups = $holder->getGroupedSecondaryHolders();
        $groups[] = array(
            'service' => $holder->getPrimaryHolder()->getService(),
            'holders' => array($holder->getPrimaryHolder()),
        );

        $services = array();
        $controls = array();
        foreach ($groups as $group) {
            $holders = array();
            $field = null;
            foreach ($group['holders'] as $baseHolder) {
                $name = $baseHolder->getName();
                foreach ($this->fields as $fieldMask) {
                    $foundControls = $this->getControl($fieldMask);
                    if (isset($foundControls[$name])) {
                        $holders[] = $baseHolder;
                        $controls[] = $foundControls[$name];
                        $field = $fieldMask;
                    } else if ($name == substr($fieldMask, 0, strpos($fieldMask, self::DELIMITER))) {
                        $holders[] = $baseHolder;
                        $controls[] = reset($foundControls); // assume single result;
                        $field = $fieldMask;
                    }
                }
            }
            if ($holders) {
                $services[] = array(
                    'service' => $group['service'],
                    'holders' => $holders,
                    'field' => $field,
                );
            }
        }

        $event = $holder->getEvent();
        $usage = 0;
        foreach ($services as $serviceData) {
            $firstHolder = reset($serviceData['holders']);
            $table = $serviceData['service']->getTable();
            $table->where($firstHolder->getEventId(), $event->getPrimary());
            $primaries = array_map(function(BaseHolder $baseHolder) {
                        return $baseHolder->getModel()->getPrimary(false);
                    }, $serviceData['holders']);
            $primaries = array_filter($primaries, function($primary) {
                        return (bool) $primary;
                    });

            $column = BaseHolder::getBareColumn($serviceData['field']);
            $pk = $table->getName() . '.' . $table->getPrimary();
            $table->where("NOT $pk IN", $primaries);
            $usage += $table->sum($column);
        }

        $capacity = $holder->getParameter($this->paramCapacity, 0);
        if ($capacity <= $usage) {
            foreach ($controls as $control) {
                $control->setDisabled();
            }
        }

        $form->onValidate[] = function(Form $form) use($capacity, $usage, $controls) {
                    $controlsUsage = 0;
                    foreach ($controls as $control) {
                        $controlsUsage += (int) $control->getValue();
                    }
                    if ($capacity < $usage + $controlsUsage) {
                        $message = str_replace('%avail', $capacity - $usage, $this->message);
                        $form->addError($message);
                    }
                };
    }

}
