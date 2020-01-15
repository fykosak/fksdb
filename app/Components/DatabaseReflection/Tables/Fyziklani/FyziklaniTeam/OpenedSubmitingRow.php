<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Utils\Html;

/**
 * Class OpenSubmitRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class OpenedSubmitingRow extends AbstractFyziklaniTeamRow {

    /**
     * @param ModelFyziklaniTeam $model
     * @inheritDoc
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')
            ->addAttributes(['class' => 'badge badge-info'])
            ->addText($model->hasOpenSubmitting() ? _('Opened') : _('Closed'));
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string {
        return _('Submit opened?');
    }
}
