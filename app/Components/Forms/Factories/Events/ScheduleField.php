<?php

namespace FKSDB\Components\Forms\Factories\Events;


use FKSDB\Components\React\IReactComponent;
use FKSDB\Components\React\ReactField;
use Nette\Forms\Controls\TextInput;

class ScheduleField extends TextInput implements IReactComponent {

    use ReactField;
    /**
     * @var array
     */
    private $data;

    public function __construct($data) {
        parent::__construct(_('Schedule'));
        $this->data = $data;
        $this->appendProperty();
    }

    public function getComponentName(): string {
        return 'schedule';
    }

    public function getModuleName(): string {
        return 'fyziklani';
    }

    public function getMode(): string {
        return '';
    }

    /**
     * @return string
     */
    public function getData(): string {
        return json_encode($this->data);
    }
}
