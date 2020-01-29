<?php

namespace FKSDB\Components\DatabaseReflection\Tables\ContestantBase;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContestant;
use Nette\Utils\Html;

/**
 * Class YearRow
 * @package FKSDB\Components\DatabaseReflection\Tables\ContestantBase
 */
class YearRow extends AbstractRow {

    /**
     * @param ModelContestant $model
     * @inheritDoc
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->year . '. ' . _('Year'));
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
        return _('Year');
    }
}
