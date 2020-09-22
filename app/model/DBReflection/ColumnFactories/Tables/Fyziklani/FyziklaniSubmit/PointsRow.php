<?php

namespace FKSDB\DBReflection\ColumnFactories\Fyziklani\FyziklaniSubmit;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
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
