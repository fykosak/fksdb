<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleGroup;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<ScheduleGroupModel>
 */
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
