<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleItem;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\NumberPrinter;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Utils\Html;

class UsedCapacityColumnFactory extends ColumnFactory
{

    /**
     * @param ModelScheduleItem $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return (new NumberPrinter(null, null, 0, NumberPrinter::NULL_VALUE_ZERO))($model->getUsedCapacity());
    }
}
