<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ITaskReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class TaskReferencedRow
 * @package FKSDB\Components\DatabaseReflection\ReferencedRows
 */
class TaskRow extends AbstractRow {

    /**
     * @inheritDoc
     * @throws BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (!$model instanceof ITaskReferencedModel) {
            throw new BadRequestException();
        }
        return Html::el('span')->addText($model->getTask()->getFQName());
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
        return _('Task');
    }
}
