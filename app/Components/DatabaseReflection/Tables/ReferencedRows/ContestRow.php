<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\Controls\Badges\ContestBadge;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IContestReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class ContestRow
 * *
 */
class ContestRow extends AbstractRow {

    /**
     * @inheritDoc
     * @throws BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (!$model instanceof IContestReferencedModel) {
            throw new BadTypeException(IContestReferencedModel::class, $model);
        }
        return ContestBadge::getHtml($model->getContest());
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
