<?php

namespace FKSDB\Components\Forms\Factories\Events;


use Events\Machine\BaseMachine;
use Events\Model\Holder\Field;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;

class ScheduleFactory extends AbstractFactory {
    /**
     * @var array
     */
    private $data;

    public function __construct($data) {
        $this->data = (array)$data;
    }

    protected function createComponent(Field $field, BaseMachine $machine, Container $container) {
        $component = new ScheduleField($this->data);
        $component->setOption('description', $field->getDescription());
        return $component;
    }

    /**
     * @param ScheduleField $component
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     */
    protected function setDefaultValue($component, Field $field, BaseMachine $machine, Container $container) {
        $component->setDefaultValue($field->getValue());
    }

    /**
     * @param ScheduleField $component
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     */
    protected function setDisabled($component, Field $field, BaseMachine $machine, Container $container) {
        $component->setDisabled();
    }

    /**
     * @param ScheduleField $component
     * @return Component|\Nette\Forms\IControl
     */
    public function getMainControl(Component $component) {
        return $component;
    }

}
