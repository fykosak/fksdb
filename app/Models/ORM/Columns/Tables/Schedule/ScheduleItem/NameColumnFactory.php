<?php

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleItem;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Utils\Html;

/**
 * Class NameColumnFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NameColumnFactory extends ColumnFactory {

    /**
     * @param AbstractModel|ModelScheduleItem $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html {
        return Html::el('span')->addText($model->name_cs . '/' . $model->name_en);
    }
}
