<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleItem;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use Fykosak\NetteORM\AbstractModel;
use Nette\Utils\Html;

class NameColumnFactory extends ColumnFactory
{

    /**
     * @param AbstractModel|ModelScheduleItem $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        return Html::el('span')->addText($model->name_cs . '/' . $model->name_en);
    }
}
