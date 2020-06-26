<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class OpenedSubmittingRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class OpenedSubmittingRow extends AbstractFyziklaniTeamRow {

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

    public function createField(...$args): BaseControl {
        throw new AbstractColumnException();
    }

    public function getTitle(): string {
        return _('Submit opened?');
    }
}
