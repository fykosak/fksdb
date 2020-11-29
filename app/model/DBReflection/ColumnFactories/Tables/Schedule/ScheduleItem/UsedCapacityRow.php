<?php

namespace FKSDB\DBReflection\ColumnFactories\Tables\Schedule\ScheduleItem;

use FKSDB\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\ValuePrinters\NumberPrinter;
use FKSDB\ORM\Models\AbstractModelSingle;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Utils\Html;

/**
 * Class UsedCapacityRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class UsedCapacityRow extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelScheduleItem $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new NumberPrinter(null, null, 0, NumberPrinter::NULL_VALUE_ZERO))($model->getUsedCapacity());
    }
}
