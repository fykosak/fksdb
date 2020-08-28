<?php

namespace FKSDB\DBReflection\ColumnFactories\Task;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelTask;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class FQNameRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FQNameRow extends AbstractColumnFactory {

    /**
     * @param AbstractModelSingle|ModelTask $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->getFQName());
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    public function getTitle(): string {
        return _('Task');
    }

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws NotImplementedException
     */
    public function createField(...$args): BaseControl {
        throw new NotImplementedException();
    }
}
