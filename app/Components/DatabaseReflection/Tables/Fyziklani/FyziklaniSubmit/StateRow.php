<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniSubmit;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use Nette\Utils\Html;

/**
 * Class StateRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniSubmit
 */
class StateRow extends AbstractFyziklaniSubmitRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('State');
    }

    /**
     * @param AbstractModelSingle|ModelFyziklaniSubmit $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        switch ($model->state) {
            case ModelFyziklaniSubmit::STATE_CHECKED:
                return Html::el('span')->addAttributes(['class' => 'badge badge-success'])->addText(_('checked'));
            default:
            case ModelFyziklaniSubmit::STATE_NOT_CHECKED:
                return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText(_('not checked'));
        }
    }
}
