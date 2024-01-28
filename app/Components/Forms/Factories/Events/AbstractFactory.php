<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Field;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

abstract class AbstractFactory implements FieldFactory
{

    public function setFieldDefaultValue(BaseControl $control, Field $field, BaseHolder $holder): void
    {
        if (!$field->isModifiable($holder)) {
            $control->setDisabled();
        }
        $this->setDefaultValue($control, $field, $holder);
        $this->appendRequiredRule($control, $field, $holder);
    }

    final protected function appendRequiredRule(BaseControl $control, Field $field, BaseHolder $holder): void
    {
        if ($field->isRequired($holder)) {
            $control->addRule(Form::FILLED, sprintf(_('%s is required.'), $field->label));
        }
    }

    protected function setDefaultValue(BaseControl $control, Field $field, BaseHolder $holder): void
    {
        $control->setDefaultValue($field->getValue($holder));
    }
}
