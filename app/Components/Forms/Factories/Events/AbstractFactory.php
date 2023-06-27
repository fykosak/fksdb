<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\Field;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

abstract class AbstractFactory implements FieldFactory
{

    public function setFieldDefaultValue(BaseControl $control, Field $field): void
    {
        if (!$field->isModifiable()) {
            $control->setDisabled();
        }
        $this->setDefaultValue($control, $field);
        $this->appendRequiredRule($control, $field);
    }

    final protected function appendRequiredRule(BaseControl $control, Field $field): void
    {
        if ($field->isRequired()) {
            $control->addRule(Form::FILLED, sprintf(_('%s is required.'), $field->label));
        }
    }

    protected function setDefaultValue(BaseControl $control, Field $field): void
    {
        $control->setDefaultValue($field->getValue());
    }
}
