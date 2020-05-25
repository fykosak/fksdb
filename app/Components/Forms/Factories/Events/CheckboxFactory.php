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
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\IControl;


/**
 * Class CheckboxFactory
 * *
 */
class CheckboxFactory extends AbstractFactory {

    /**
     * @param Field $field
     * @param Container $container
     * @return Checkbox
     */
    protected function createComponent(Field $field, Container $container) {
        $component = new Checkbox($field->getLabel());
        $component->setOption('description', $field->getDescription());
        return $component;
    }


    /**
     * @param IControl $component
     * @param Field $field
     * @param Container $container
     * @return void
     */
    protected function setDefaultValue($component, Field $field, Container $container) {
        $component->setDefaultValue($field->getValue());
    }

    /**
     * @param IControl $component
     * @param Field $field
     * @param Container $container
     * @return void
     */
    protected function setDisabled($component, Field $field, Container $container) {
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
