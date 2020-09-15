<?php

namespace FKSDB\DBReflection\ColumnFactories\Schedule\ScheduleGroup;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use Nette\Utils\Html;

/**
 * Class NameColumnFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NameColumnFactory extends DefaultColumnFactory {
    /**
     * @param AbstractModelSingle|ModelScheduleGroup $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->name_cs . '/' . $model->name_en);
    }
}
