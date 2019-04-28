<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\Forms\Controls\DateInputs\DateTimeLocalInput;
use Nette\Forms\Controls\BaseControl;

/**
 * Class RegistrationBeginRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class RegistrationBeginRow extends AbstractEventRowFactory {

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Registration begin');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        return new DateTimeLocalInput(self::getTitle());
    }

}
