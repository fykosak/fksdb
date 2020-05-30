<?php


namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem;

use FKSDB\Components\DatabaseReflection\ValuePrinters\BinaryPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Utils\Html;

/**
 * Class RequireIdNumberRow
 * *
 */
class RequireIdNumberRow extends AbstractScheduleItemRow {

    public function getTitle(): string {
        return _('Require Id number');
    }

    /**
     * @param AbstractModelSingle|ModelScheduleItem $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new BinaryPrinter())($model->require_id_number);
    }
}
