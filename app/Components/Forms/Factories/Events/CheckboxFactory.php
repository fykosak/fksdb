<?php
/**
 * Created by PhpStorm.
 * User: miso
 * Date: 12.11.2016
 * Time: 14:03
 */

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Model\Holder\Field;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\IControl;


/**
 * Class CheckboxFactory
 * @package FKSDB\Components\Forms\Factories\Events
 */
class CheckboxFactory extends AbstractFactory {
    public function __construct() {
    }

    /**
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     * @return Checkbox
     */
    protected function createComponent(Field $field, BaseMachine $machine, Container $container) {

        $component = new Checkbox($field->getLabel());
        $component->setOption('description', $field->getDescription());

        return $component;
    }


    /**
     * @param $component
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     * @return void
     */
    protected function setDefaultValue($component, Field $field, BaseMachine $machine, Container $container) {
        $component->setDefaultValue($field->getValue());
    }

    /**
     * @param $component
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     * @return void
     */
    protected function setDisabled($component, Field $field, BaseMachine $machine, Container $container) {
        $component->setDisabled();
    }

    /**
     * @param Component $component
     * @return Component|IControl
     */
    public function getMainControl(Component $component) {
        return $component;
    }

}
