<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<TeamModel2>
 */
class OpenedSubmittingColumnFactory extends AbstractColumnFactory
{
    /**
     * @param TeamModel2 $model
     */
    protected function createHtmlValue(Model $model): Html
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
