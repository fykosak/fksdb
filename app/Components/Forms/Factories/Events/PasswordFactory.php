<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Events\Model\Holder\Field;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IComponent;
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
     * @return TextInput
     */
    public function createComponent(Field $field): IComponent {
        $element = new TextInput($field->getLabel());
        $element->setType('password');
        $element->setOption('description', $field->getDescription());
        return $element;
    }

    /**
     * @param TextInput|IComponent $component
     * @param Field $field
     */
    protected function setDefaultValue(IComponent $component, Field $field) {
        $component->setDefaultValue('');
    }

    /**
     * @param TextInput|IComponent $component
     */
    protected function setDisabled(IComponent $component) {
        $component->setDisabled();
    }

    /**
     * @param Component|IComponent $component
     * @return Component|IControl
     */
    public function getMainControl(IComponent $component): IControl {
        return $component;
    }

}
