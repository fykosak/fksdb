<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\AbstractModel;
use Nette\Utils\Html;

class NameNIdColumnFactory extends ColumnFactory
{
    /**
     * @param TeamModel2 $model
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        return Html::el('span')->addText($model->name . ' (' . $model->fyziklani_team_id . ')');
    }
}
