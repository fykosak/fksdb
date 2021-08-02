<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\DataValidator;
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
        $container = $control->getParent();
        if ($field->isRequired()) {
            $conditioned = $control;
            foreach ($field->getBaseHolder()->getDeterminingFields() as $name => $determiningField) {
                if ($determiningField === $field) {
                    $conditioned = $control;
                    break;
                }
                /*
                 * NOTE: If the control doesn't exists, it's hidden and as such cannot condition further requirements.
                 */
                if (isset($container[$name])) {
                    $conditioned = $conditioned->addConditionOn($container[$name], Form::FILLED);
                }
            }
            $conditioned->addRule(Form::FILLED, sprintf(_('%s is required.'), $field->getLabel()));
        }
    }

    public function validate(Field $field, DataValidator $validator): void
    {
        if ($field->isRequired() && ($field->getValue() === '' || $field->getValue() === null)) {
            $validator->addError(sprintf(_('%s is required'), $field->getLabel()));
        }
    }

    protected function setDefaultValue(BaseControl $control, Field $field): void
    {
        $control->setDefaultValue($field->getValue());
    }
}
