<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Utils\Html;

/**
 * Class OpenSubmitRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class OpenedSubmittingRow extends AbstractFyziklaniTeamRow {

    /**
     * @param ModelFyziklaniTeam $model
     * @inheritDoc
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $html = Html::el('span');
        if ($model->hasOpenSubmitting()) {
            $html->addAttributes(['class' => 'badge badge-1'])
                ->addText(_('Opened'));
        } else {
            $html->addAttributes(['class' => 'badge badge-3'])
                ->addText(_('Closed'));
        }
        return $html;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string {
        return _('Submit opened?');
    }
}
