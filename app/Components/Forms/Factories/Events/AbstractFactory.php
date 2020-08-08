<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Events\Model\Holder\DataValidator;
use FKSDB\Events\Model\Holder\Field;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractFactory implements IFieldFactory {

    /**
     * @param IComponent $component
     * @param Field $field
     * @return void
     */
    public function setFieldDefaultValue(IComponent $component, Field $field) {
        if (!$field->isModifiable()) {
            $this->setDisabled($component);
        }
        $this->setDefaultValue($component, $field);
        $this->appendRequiredRule($component, $field);
    }

    /**
     * @param IComponent $component
     * @param Field $field
     * @return void
     */
    final protected function appendRequiredRule(IComponent $component, Field $field) {
        $container = $component->getParent();
        $control = $this->getMainControl($component);
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
                    $control = $determiningField->getMainControl($container[$name]);
                    $conditioned = $conditioned->addConditionOn($control, Form::FILLED);
                }
            }
            $conditioned->addRule(Form::FILLED, sprintf(_('%s is required.'), $field->getLabel()));
        }
    }

    /**
     * @param Field $field
     * @param DataValidator $validator
     * @return bool|void TODO what is the return type?
     */
    public function validate(Field $field, DataValidator $validator) {
        if ($field->isRequired() && ($field->getValue() === '' || $field->getValue() === null)) {
            $validator->addError(sprintf(_('%s is required'), $field->getLabel()));
        }
    }

    /**
     * @param IComponent $component
     * @return void
     */
    abstract protected function setDisabled(IComponent $component);

    /**
     * @param IComponent $component
     * @param Field $field
     * @return void
     */
    abstract protected function setDefaultValue(IComponent $component, Field $field);
}
