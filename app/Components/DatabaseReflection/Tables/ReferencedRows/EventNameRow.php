<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\Transitions\IEventReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

class EventNameRow extends AbstractRow {

    /**
     * @param AbstractModelSingle $model
     * @return Html
     * @throws BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (!$model instanceof IEventReferencedModel) {
            throw new BadRequestException();
        }
        return Html::el('span')->addText($model->getEvent()->name);
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
        return _('Event');
    }
}
