<?php

namespace FKSDB\DBReflection\ColumnFactories\Tables\Schedule\ScheduleItem;

use FKSDB\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\ValuePrinters\NumberPrinter;
use FKSDB\ORM\Models\AbstractModelSingle;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
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
