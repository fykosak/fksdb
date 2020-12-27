<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Utils\Html;

/**
 * Class NameNIdRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NameNIdRow extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelFyziklaniTeam $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->name . ' (' . $model->e_fyziklani_team_id . ')');
    }
}
