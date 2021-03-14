<?php

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Utils\Html;

/**
 * Class OpenedSubmittingRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class OpenedSubmittingColumnFactory extends ColumnFactory {

    /**
     * @param AbstractModel|ModelFyziklaniTeam $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html {
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
