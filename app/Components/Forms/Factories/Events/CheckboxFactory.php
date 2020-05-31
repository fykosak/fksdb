<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Model\Holder\Field;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\IControl;

/**
 * Class CheckboxFactory
 * *
 */
class CheckboxFactory extends AbstractFactory {

    /**
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     * @return Checkbox
     */
    protected function createComponent(Field $field, BaseMachine $machine, Container $container) {

        $component = new Checkbox($field->getLabel());
        $component->setOption('description', $field->getDescription());

        return $component;
    }


    /**
     * @param BaseControl $component
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     * @return void
     */
    protected function setDefaultValue($component, Field $field, BaseMachine $machine, Container $container): void {
        $component->setDefaultValue($field->getValue());
    }

    /**
     * @param BaseControl $component
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     * @return void
     */
    protected function setDisabled($component, Field $field, BaseMachine $machine, Container $container): void {
        $component->setDisabled();
    }

    /**
     * @param Component $component
     * @return Component|IControl
     */
    public function getMainControl(Component $component) {
        return $component;
    }

}
