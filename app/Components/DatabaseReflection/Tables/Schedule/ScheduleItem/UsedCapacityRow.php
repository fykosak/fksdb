<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Utils\Html;

/**
 * Class UsedCapacityRow
 * *
 */
class UsedCapacityRow extends AbstractScheduleItemRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Used capacity');
    }

    /**
     * @param AbstractModelSingle|ModelScheduleItem $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->getUsedCapacity());
    }
}
