<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\Forms\Controls\DateInputs\DateTimeLocalInput;
use Nette\Forms\Controls\BaseControl;

/**
 * Class RegistrationEndRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class RegistrationEndRow extends AbstractEventRowFactory {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Registration end');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        return new DateTimeLocalInput(self::getTitle());
    }
}
