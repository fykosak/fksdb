<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IContestReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class ContestRow
 * @package FKSDB\Components\DatabaseReflection\ReferencedRows
 */
class ContestRow extends AbstractRow {

    /**
     * @param AbstractModelSingle $model
     * @return Html
     * @throws BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (!$model instanceof IContestReferencedModel) {
            throw new BadRequestException();
        }
        $component = Html::el('span');
        switch ($model->getContest()->contest_id) {
            case 1:
                return $component->addAttributes(['class' => 'badge badge-fykos'])->addText(_('FYKOS'));
            case 2:
                return $component->addAttributes(['class' => 'badge badge-vyfuk'])->addText(_('Výfuk'));
        }
        throw new BadRequestException();
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Contest');
    }
}