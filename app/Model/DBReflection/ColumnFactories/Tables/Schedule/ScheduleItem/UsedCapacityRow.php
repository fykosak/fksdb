<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Tables\Schedule\ScheduleItem;

use FKSDB\Model\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Model\ValuePrinters\NumberPrinter;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\Schedule\ModelScheduleItem;
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
