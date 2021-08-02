<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleItem;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Models\ValuePrinters\NumberPrinter;
use Fykosak\NetteORM\AbstractModel;
use Nette\Utils\Html;

class UsedCapacityColumnFactory extends ColumnFactory
{

    /**
     * @param AbstractModel|ModelScheduleItem $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        return (new NumberPrinter(null, null, 0, NumberPrinter::NULL_VALUE_ZERO))($model->getUsedCapacity());
    }
}
