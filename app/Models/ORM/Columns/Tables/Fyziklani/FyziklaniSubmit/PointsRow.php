<?php

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniSubmit;

use FKSDB\Models\ORM\Columns\Types\DefaultColumnFactory;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use Nette\Utils\Html;

/**
 * Class PointsRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PointsRow extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelFyziklaniSubmit $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $el = Html::el('span');
        if (!\is_null($model->points)) {
            return $el->addText($model->points);
        }
        return $el->addAttributes(['class' => 'badge badge-warning'])->addText(_('revoked'));
    }
}
