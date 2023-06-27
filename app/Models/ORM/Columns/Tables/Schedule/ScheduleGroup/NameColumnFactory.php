<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleGroup;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use Nette\Utils\Html;

class NameColumnFactory extends ColumnFactory
{
    /**
     * @param ScheduleGroupModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return Html::el('span')->addText($model->name_cs . '/' . $model->name_en);
    }
}
