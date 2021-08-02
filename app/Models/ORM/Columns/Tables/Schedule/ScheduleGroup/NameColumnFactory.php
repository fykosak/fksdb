<?php

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleGroup;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use Fykosak\NetteORM\AbstractModel;
use Nette\Utils\Html;

class NameColumnFactory extends ColumnFactory
{
    /**
     * @param AbstractModel|ModelScheduleGroup $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        return Html::el('span')->addText($model->name_cs . '/' . $model->name_en);
    }
}
