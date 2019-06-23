<?php

namespace FKSDB\Components\Forms\Factories\Events;


use FKSDB\Components\React\IReactComponent;
use FKSDB\Components\React\ReactField;
use Nette\Forms\Controls\TextInput;

/**
 * Class ScheduleField
 * @package FKSDB\Components\Forms\Factories\Events
 */
class ScheduleField extends TextInput implements IReactComponent {

    use ReactField;
    /**
     * @var array
     */
    private $data;

    /**
     * ScheduleField constructor.
     * @param $data
     */
    public function __construct($data) {
        parent::__construct(_('Schedule'));
        $this->data = $data;
        $this->appendProperty();
        $this->registerMonitor();
    }

    /**
     * @return string
     */
    public function getComponentName(): string {
        return 'schedule';
    }

    /**
     * @return string
     */
    public function getModuleName(): string {
        return 'fyziklani';
    }

    /**
     * @return string
     */
    public function getMode(): string {
        return '';
    }

    /**
     * @return string
     */
    public function getData(): string {
        return json_encode($this->data);
    }

    /**
     * @param $obj
     */
    public function attached($obj) {
        parent::attached($obj);
        $this->attachedReact($obj);
    }

    /**
     * @return array
     */
    public function getActions(): array {
        return [];
    }
}
