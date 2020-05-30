<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniGameSetup;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Utils\Html;

/**
 * Class RefreshDelayRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class RefreshDelayRow extends AbstractFyziklaniGameSetupRow {

    public function getTitle(): string {
        return _('Refresh delay');
    }

    /**
     * @param AbstractModelSingle|ModelFyziklaniGameSetup $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addHtml(number_format($model->refresh_delay, 0, null, '&#8287;') . '&#8287;ms');
    }
}
