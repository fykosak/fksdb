<?php

namespace FKSDB\Components\Forms\Factories\Events;

use Events\Machine\BaseMachine;
use Events\Model\Field;
use Nette\ComponentModel\Component;
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

        $this->setDisabled($component, $field, $machine, $container);
        $this->setDefaultValue($component, $field, $machine, $container);

        $control = $this->getMainControl($component);
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
            $conditioned->addRule(Form::FILLED, _('%label je povinná položka.'));
        }
    }

    abstract protected function setDisabled(Component $component, Field $field, BaseMachine $machine, Container $container);

    abstract protected function setDefaultValue(Component $component, Field $field, BaseMachine $machine, Container $container);

    abstract protected function createComponent(Field $field, BaseMachine $machine, Container $container);
}
