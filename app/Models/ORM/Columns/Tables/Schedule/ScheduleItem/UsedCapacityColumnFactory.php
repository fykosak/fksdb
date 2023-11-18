<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleItem;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\UI\NumberPrinter;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<ScheduleItemModel,never>
 */
class UsedCapacityColumnFactory extends AbstractColumnFactory
{
    /**
     * @param ScheduleItemModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return (new NumberPrinter(null, null, 0, NumberPrinter::NULL_VALUE_ZERO))($model->getUsedCapacity());
    }
}
