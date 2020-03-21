<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniSubmit;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use Nette\Utils\Html;

/**
 * Class PointsRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniSubmit
 */
class PointsRow extends AbstractFyziklaniSubmitRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Points');
    }

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
