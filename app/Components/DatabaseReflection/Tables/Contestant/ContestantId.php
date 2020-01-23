<?php

namespace FKSDB\Components\DatabaseReflection\Tables\ContestantBase;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContestant;
use Nette\Utils\Html;

/**
 * Class ContestantId
 * @package FKSDB\Components\DatabaseReflection\Tables\ContestantBase
 */
class ContestantId extends AbstractRow {

    /**
     * @param ModelContestant $model
     * @inheritDoc
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText('#' . $model->ct_id);
    }

    /**
     * @inheritDoc
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string {
        return _('Contestant id');
    }
}
