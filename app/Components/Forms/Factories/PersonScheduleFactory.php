<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\Schedule\ScheduleField;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\RadioList;
use Tracy\Debugger;

/**
 * Class PersonScheduleFactory
 * @package FKSDB\Components\Forms\Factories
 */
class PersonScheduleFactory {
    /**
     * @param $fieldName
     * @param ModelEvent $event
     * @return BaseControl
     */
    public function createField($fieldName, ModelEvent $event) {
        return new ScheduleField($event, $fieldName);
    }
}
