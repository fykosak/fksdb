<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\Field;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;

class CheckboxFactory extends AbstractFactory {

    public function createComponent(Field $field): Checkbox {
        $component = new Checkbox($field->getLabel());
        $component->setOption('description', $field->getDescription());
        return $component;
    }

    protected function setDefaultValue(BaseControl $control, Field $field): void {
        $control->setDefaultValue($field->getValue());
    }

    protected function setDisabled(BaseControl $component): void {
        $component->setDisabled();
        $component->setOmitted(false);
    }
}
