<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Types\DateTime;

use FKSDB\Components\Forms\Controls\DateInputs\DateTimeLocalInput;
use Nette\Forms\Controls\BaseControl;

/**
 * Class DateTimeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DateTimeColumnFactory extends AbstractDateTimeColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        return new DateTimeLocalInput($this->getTitle());
    }

    protected function getDefaultFormat(): string {
        return _('__date_time');
    }
}
