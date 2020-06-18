<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\Controls\Badges\ContestBadge;
use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContest;
use Nette\Utils\Html;

/**
 * Class ContestRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ContestRow extends AbstractColumnFactory {

    /**
     * @param AbstractModelSingle|ModelContest $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return ContestBadge::getHtml($model);
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    public function getTitle(): string {
        return _('Contest');
    }
}
