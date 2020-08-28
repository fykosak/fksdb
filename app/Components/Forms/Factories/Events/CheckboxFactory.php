<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Events\Model\Holder\Field;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\IControl;

/**
 * Class CheckboxFactory
 * *
 */
class CheckboxFactory extends AbstractFactory {

    public function createComponent(Field $field): Checkbox {
        $component = new Checkbox($field->getLabel());
        $component->setOption('description', $field->getDescription());
        return $component;
    }

    /**
     * @param BaseControl|IComponent $component
     * @param Field $field
     * @return void
     */
    protected function setDefaultValue(IComponent $component, Field $field): void {
        $component->setDefaultValue($field->getValue());
    }

    /**
     * @param BaseControl|IComponent $component
     * @return void
     */
    protected function setDisabled(IComponent $component): void {
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
