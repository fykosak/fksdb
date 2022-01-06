<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Utils\Html;

class NameNIdColumnFactory extends ColumnFactory {

    /**
     * @param AbstractModel|ModelFyziklaniTeam $model
     */
    protected function createHtmlValue(AbstractModel $model): Html {
        return Html::el('span')->addText($model->name . ' (' . $model->e_fyziklani_team_id . ')');
    }
}
