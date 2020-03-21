<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Utils\Html;

/**
 * Class FreeCapacityRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem
 */
class FreeCapacityRow extends AbstractScheduleItemRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Free capacity');
    }

    /**
     * @param AbstractModelSingle|ModelScheduleItem $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        try {
            return Html::el('span')->addText($model->getAvailableCapacity());
        } catch (\LogicException $e) {
            return Html::el('span')->addHtml('&#8734;');
        }
    }
}