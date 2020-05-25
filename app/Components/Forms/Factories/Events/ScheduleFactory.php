<?php

namespace FKSDB\Components\Forms\Factories\Events;


use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Model\Holder\Field;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\IControl;
use Nette\Utils\JsonException;

/**
 * Class ScheduleFactory
 * *
 */
class ScheduleFactory extends AbstractFactory {
    /**
     * @var array
     */
    private $data;

    /**
     * ScheduleFactory constructor.
     * @param $data
     * @param $visible
     */
    public function __construct($data, $visible) {
        $this->data = [
            'data' => (array)$data,
            'visible' => $visible,
        ];
    }

    /**
     * @param Field $field
     * @param Container $container
     * @return ScheduleField
     * @throws JsonException
     */
    protected function createComponent(Field $field, Container $container) {
        $component = new ScheduleField($this->data);
        $component->setOption('description', $field->getDescription());
        return $component;
    }

    /**
     * @param IControl $component
     * @param Field $field
     * @param Container $container
     */
    protected function setDefaultValue($component, Field $field, Container $container) {
        $component->setDefaultValue($field->getValue());
    }

    /**
     * @param IControl $component
     * @param Field $field
     * @param Container $container
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
