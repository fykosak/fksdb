<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Types\DateTime;

use FKSDB\Components\Forms\Controls\DateInputs\TimeInput;
use Nette\Forms\Controls\BaseControl;

/**
 * Class DateTimeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TimeRow extends AbstractDateTimeColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        return new TimeInput($this->getTitle());
    }

    protected function getDefaultFormat(): string {
        return _('__time');
    }
}
