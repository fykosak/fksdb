<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleGroup;

use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use Nette\Utils\Html;

/**
 * Class EndRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Schedule
 */
class EndRow extends AbstractScheduleGroupRow {

    public function getTitle(): string {
        return _('Schedule end');
    }

    /**
     * @param AbstractModelSingle|ModelScheduleGroup $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new DatePrinter('d. m. Y H:i'))($model->end);
    }
}
