<?php

namespace FKSDB\Components\Forms\Factories\Events;

use Events\Machine\BaseMachine;
use Events\Model\Holder\DataValidator;
use Events\Model\Holder\Field;
use Nette\Forms\Container;
use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractFactory implements IFieldFactory {

    public function create(Field $field, BaseMachine $machine, Container $container) {
        $component = $this->createComponent($field, $machine, $container);

        if (!$field->isModifiable($machine)) {
            $this->setDisabled($component, $field, $machine, $container);
        }
        $this->setDefaultValue($component, $field, $machine, $container);

        $control = $this->getMainControl(is_array($component) ? reset($component) : $component);
        $this->appendRequiredRule($control, $field, $machine, $container);

        return $component;
    }

    protected final function appendRequiredRule(IControl $element, Field $field, BaseMachine $machine, Container $container) {
        if ($field->isRequired($machine)) {
            $conditioned = $element;
            foreach ($field->getBaseHolder()->getDeterminingFields() as $name => $determiningField) {
                if ($determiningField === $field) {
                    $conditioned = $element;
                    break;
                }
                $control = $determiningField->getMainControl($container[$name]); // existence is ensured via check in EventsExtension
                $conditioned = $conditioned->addConditionOn($control, Form::FILLED);
            }
            $conditioned->addRule(Form::FILLED, sprintf(_('%s je povinná položka.'), $field->getLabel()));
        }
    }

    public function validate(Field $field, DataValidator $validator) {
        if ($field->isRequired() && ($field->getValue() === '' || $field->getValue() === null)) {
            $validator->addError(sprintf(_('%s je povinná položka.'), $field->getLabel()));
        }
    }

    abstract protected function setDisabled($component, Field $field, BaseMachine $machine, Container $container);

    abstract protected function setDefaultValue($component, Field $field, BaseMachine $machine, Container $container);

    abstract protected function createComponent(Field $field, BaseMachine $machine, Container $container);
}
