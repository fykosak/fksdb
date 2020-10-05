<?php

namespace FKSDB\DBReflection\ColumnFactories\Fyziklani\FyziklaniTeam;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Utils\Html;

/**
 * Class OpenedSubmittingRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class OpenedSubmittingRow extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelFyziklaniTeam $model
     * @return Html
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
}
