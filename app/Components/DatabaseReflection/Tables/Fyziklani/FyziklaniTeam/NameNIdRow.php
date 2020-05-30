<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Utils\Html;

/**
 * Class FyziklaniTeamIdTeamRow
 * *
 */
class NameNIdRow extends AbstractFyziklaniTeamRow {

    public function getTitle(): string {
        return _('Team');
    }

    /**
     * @param AbstractModelSingle|ModelFyziklaniTeam $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->name . ' (' . $model->e_fyziklani_team_id . ')');
    }

}
