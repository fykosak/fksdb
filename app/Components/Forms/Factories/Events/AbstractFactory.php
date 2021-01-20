<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\DataValidator;
use FKSDB\Models\Events\Model\Holder\Field;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractFactory implements IFieldFactory {

    public function setFieldDefaultValue(BaseControl $control, Field $field): void {
        if (!$field->isModifiable()) {
            $this->setDisabled($control);
        }
        $this->setDefaultValue($control, $field);
        $this->appendRequiredRule($control, $field);
    }

    final protected function appendRequiredRule(BaseControl $control, Field $field): void {
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

    /**
     * @param Field $field
     * @param DataValidator $validator
     * @return mixed|bool|void TODO what is the return type?
     */
    public function validate(Field $field, DataValidator $validator): void {
        if ($field->isRequired() && ($field->getValue() === '' || $field->getValue() === null)) {
            $validator->addError(sprintf(_('%s is required'), $field->getLabel()));
        }
    }

    abstract protected function setDisabled(BaseControl $control): void;

    abstract protected function setDefaultValue(BaseControl $control, Field $field): void;
}
