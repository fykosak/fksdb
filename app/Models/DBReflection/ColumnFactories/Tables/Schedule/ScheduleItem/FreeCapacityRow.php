<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\Schedule\ScheduleItem;

use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\ValuePrinters\NumberPrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Utils\Html;

/**
 * Class FreeCapacityRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FreeCapacityRow extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelScheduleItem $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $capacity = null;
        try {
            $capacity = $model->getAvailableCapacity();
        } catch (\LogicException $e) {
        }
        return (new NumberPrinter(null, null, 0, NumberPrinter::NULL_VALUE_INF))($capacity);
    }
}
