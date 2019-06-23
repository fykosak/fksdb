<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\Controls\Helpers\Badges\ContestBadge;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelOrg;
use Nette\Utils\Html;

/**
 * Class ContestRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class ContestRow extends AbstractOrgRowFactory {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Contest');
    }

    /**
     * @param AbstractModelSingle|ModelOrg $model
     * @return Html
     * @throws \Nette\Application\BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return ContestBadge::getHtml($model->contest_id);
    }
}
