<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleItem;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\UI\NumberPrinter;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<ScheduleItemModel>
 */
class FreeCapacityColumnFactory extends AbstractColumnFactory
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
