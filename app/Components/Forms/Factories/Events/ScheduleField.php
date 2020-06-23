<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Components\React\ReactField;
use Nette\DeprecatedException;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\JsonException;

/**
 * Class ScheduleField
 * *
 * @deprecated
 */
class ScheduleField extends TextInput {

    use ReactField;
    /**
     * @var array
     */
    private $data;

    /**
     * ScheduleField constructor.
     * @param $data
     * @throws JsonException
     */
    public function __construct($data) {
        parent::__construct(_('Schedule'));
        $this->data = $data;
        $this->appendProperty();
        $this->registerMonitor();
    }

    protected function getReactId(...$args): string {
        throw new DeprecatedException();
    }

    public function getData(...$args): string {
        return json_encode($this->data);
    }
}
