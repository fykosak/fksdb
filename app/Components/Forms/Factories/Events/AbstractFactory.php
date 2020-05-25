<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Events\Model\Holder\DataValidator;
use FKSDB\Events\Model\Holder\Field;
use Nette\Forms\Container;
use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractFactory implements IFieldFactory {

    /**
     * @param Field $field
     * @param Container $container
     * @return array|mixed
     */
    public function create(Field $field, Container $container) {
        $component = $this->createComponent($field, $container);

        if (!$field->isModifiable()) {
            $this->setDisabled($component, $field, $container);
        }
        $this->setDefaultValue($component, $field, $container);

        $control = $this->getMainControl(is_array($component) ? reset($component) : $component);
        $this->appendRequiredRule($control, $field, $container);

        return $component;
    }

    /**
     * @param IControl $element
     * @param Field $field
     * @param Container $container
     * @return void
     */
    final protected function appendRequiredRule(IControl $element, Field $field, Container $container) {
        if ($field->isRequired()) {
            $conditioned = $element;
            foreach ($field->getBaseHolder()->getDeterminingFields() as $name => $determiningField) {
                if ($determiningField === $field) {
                    $conditioned = $element;
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
            $conditioned->addRule(Form::FILLED, sprintf(_('%s je povinná položka.'), $field->getLabel()));
        }
    }

    /**
     * @param Field $field
     * @param DataValidator $validator
     * @return bool|void
     */
    public function validate(Field $field, DataValidator $validator) {
        if ($field->isRequired() && ($field->getValue() === '' || $field->getValue() === null)) {
            $validator->addError(sprintf(_('%s je povinná položka.'), $field->getLabel()));
        }
    }

    /**
     * @param IControl $component
     * @param Field $field
     * @param Container $container
     * @return void
     */
    abstract protected function setDisabled($component, Field $field, Container $container);

    /**
     * @param IControl $component
     * @param Field $field
     * @param Container $container
     * @return void
     */
    abstract protected function setDefaultValue($component, Field $field, Container $container);

    /**
     * @param Field $field
     * @param Container $container
     * @return IControl
     */
    abstract protected function createComponent(Field $field, Container $container);
}
