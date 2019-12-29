<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\Controls\Helpers\Badges\ContestBadge;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IContestReferencedModel;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelOrg;
use Nette\Application\BadRequestException;
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
     * @param AbstractModelSingle|ModelOrg $model
     * @return Html
     * @throws BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $contest = null;
        if ($model instanceof ModelContest) {
            $contest = $model;
        } else if ($model instanceof IContestReferencedModel) {
            $contest = $model->getContest();
        } else {
            throw new BadRequestException();
        }
        $component = Html::el('span');
        switch ($contest->contest_id) {
            case 1:
                return $component->addAttributes(['class' => 'badge badge-fykos'])->addText(_('FYKOS'));
            case 2:
                return $component->addAttributes(['class' => 'badge badge-vyfuk'])->addText(_('Výfuk'));
        }
        throw new BadRequestException();
    }

    /**
     * @inheritDoc
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
