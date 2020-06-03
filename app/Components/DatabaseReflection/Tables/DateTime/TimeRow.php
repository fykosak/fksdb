<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Forms\Controls\DateInputs\TimeInput;
use Nette\Forms\Controls\BaseControl;

/**
 * Class DateTimeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TimeRow extends AbstractDateTimeRow {

    public function createFormControl(...$args): BaseControl {
        return new TimeInput($this->getTitle());
    }

    protected function getDefaultFormat(): string {
        return _('__time');
    }
}
