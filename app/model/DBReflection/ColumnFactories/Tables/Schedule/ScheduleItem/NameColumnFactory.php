<?php

namespace FKSDB\DBReflection\ColumnFactories\Schedule\ScheduleItem;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class NameColumnFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NameColumnFactory extends AbstractColumnFactory {

    public function getTitle(): string {
        return _('Item name');
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    /**
     * @param array $args
     * @return BaseControl
     * @throws NotImplementedException
     */
    public function createField(...$args): BaseControl {
        throw new NotImplementedException();
    }

    /**
     * @param AbstractModelSingle|ModelScheduleItem $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->name_cs . '/' . $model->name_en);
    }
}
