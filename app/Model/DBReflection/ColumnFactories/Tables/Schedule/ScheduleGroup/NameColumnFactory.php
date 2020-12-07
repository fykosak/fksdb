<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Tables\Schedule\ScheduleGroup;

use FKSDB\Model\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\Schedule\ModelScheduleGroup;
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
