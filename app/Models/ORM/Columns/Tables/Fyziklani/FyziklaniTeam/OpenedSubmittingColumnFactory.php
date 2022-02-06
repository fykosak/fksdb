<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use Nette\Utils\Html;

class OpenedSubmittingColumnFactory extends ColumnFactory
{

    /**
     * @param TeamModel $model
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        $html = Html::el('span');
        if ($model->hasOpenSubmitting()) {
            $html->addAttributes(['class' => 'badge bg-color-1'])
                ->addText(_('Opened'));
        } else {
            $html->addAttributes(['class' => 'badge bg-color-3'])
                ->addText(_('Closed'));
        }
        return $html;
    }
}
