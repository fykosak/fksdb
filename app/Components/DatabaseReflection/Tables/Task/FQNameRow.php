<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Task;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelTask;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class FQNameRow
 */
class FQNameRow extends AbstractRow {

    /**
     * @inheritDoc
     * @param ModelTask $model
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->getFQName());
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

    /**
     * @param array $args
     * @return BaseControl
     * @throws BadRequestException
     */
    public function createField(...$args): BaseControl {
        throw new BadRequestException();
    }
}
