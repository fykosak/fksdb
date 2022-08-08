<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleItem;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\NumberPrinter;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Nette\Utils\Html;

class FreeCapacityColumnFactory extends ColumnFactory
{

    /**
     * @param ScheduleItemModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        $capacity = null;
        try {
            $capacity = $model->getAvailableCapacity();
        } catch (\LogicException $e) {
        }
        return (new NumberPrinter(null, null, 0, NumberPrinter::NULL_VALUE_INF))($capacity);
    }
}
