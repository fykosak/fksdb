<?php

namespace FKSDB\Components\Forms\Factories\Events;

use Events\Machine\BaseMachine;
use Events\Model\Holder\Field;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\IControl;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PasswordFactory extends AbstractFactory {

    /**
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     * @return TextInput
     */
    protected function createComponent(Field $field, BaseMachine $machine, Container $container) {
        $element = new TextInput($field->getLabel());
        $element->setType('password');
        $element->setOption('description', $field->getDescription());
        return $element;
    }

    /**
     * @param TextInput $component
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     */
    protected function setDefaultValue($component, Field $field, BaseMachine $machine, Container $container) {
        $component->setDefaultValue('');
    }

    /**
     * @param TextInput $component
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     */
    protected function setDisabled($component, Field $field, BaseMachine $machine, Container $container) {
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

