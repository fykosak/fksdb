<?php

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleItem;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\NumberPrinter;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Utils\Html;

/**
 * Class FreeCapacityRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FreeCapacityColumnFactory extends ColumnFactory {

    /**
     * @param AbstractModel|ModelScheduleItem $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html {
        $capacity = null;
        try {
            $capacity = $model->getAvailableCapacity();
        } catch (\LogicException $e) {
        }
        return (new NumberPrinter(null, null, 0, NumberPrinter::NULL_VALUE_INF))($capacity);
    }
}
