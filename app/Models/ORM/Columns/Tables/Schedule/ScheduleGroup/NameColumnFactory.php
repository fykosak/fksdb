<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleGroup;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use Nette\Utils\Html;

class NameColumnFactory extends ColumnFactory {
    /**
     * @param AbstractModel|ModelScheduleGroup $model
     */
    protected function createHtmlValue(AbstractModel $model): Html {
        return Html::el('span')->addText($model->name_cs . '/' . $model->name_en);
    }
}
