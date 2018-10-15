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

    public function __construct(array $data) {
        $this->data = $data;
    }

    protected function createComponent(Field $field, BaseMachine $machine, Container $container) {
        return new ScheduleField($this->data);
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
