<?php
/**
 * Created by PhpStorm.
 * User: miso
 * Date: 12.11.2016
 * Time: 14:03
 */

namespace FKSDB\Components\Forms\Factories\Events;

use Events\Machine\BaseMachine;
use Events\Model\Holder\Field;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\Controls\Checkbox;


class CheckboxFactory extends AbstractFactory {
    public function __construct() {
    }

    protected function createComponent(Field $field, BaseMachine $machine, Container $container) {

        $component = new Checkbox($field->getLabel());
        $component->setOption('description', $field->getDescription());

        return $component;
    }


    protected function setDefaultValue($component, Field $field, BaseMachine $machine, Container $container) {
        $component->setDefaultValue($field->getValue());
    }

    protected function setDisabled($component, Field $field, BaseMachine $machine, Container $container) {
        $component->setDisabled();
    }

    public function getMainControl(Component $component) {
        return $component;
    }

}