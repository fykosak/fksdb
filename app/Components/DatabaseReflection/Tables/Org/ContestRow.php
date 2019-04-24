<?php


namespace FKSDB\Components\DatabaseReflection\Org;


use FKSDB\Components\Controls\Helpers\Badges\ContestBadge;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelOrg;
use Nette\Utils\Html;

/**
 * Class ContestRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class ContestRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Contest');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @param AbstractModelSingle|ModelOrg $model
     * @param string $fieldName
     * @return Html
     * @throws \Nette\Application\BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        return ContestBadge::getHtml($model->contest_id);
    }
}
