<?php

namespace FKSDB\Components\DatabaseReflection\Tables\ContestantBase;

use FKSDB\Components\Controls\Helpers\Badges\ContestBadge;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContestant;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class ContestId
 * @package FKSDB\Components\DatabaseReflection\Tables\ContestantBase
 */
class ContestId extends AbstractRow {

    /**
     * @param ModelContestant $model
     * @inheritDoc
     * @throws BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return ContestBadge::getHtml($model->contest_id);
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
        return _('Contest');
    }
}
