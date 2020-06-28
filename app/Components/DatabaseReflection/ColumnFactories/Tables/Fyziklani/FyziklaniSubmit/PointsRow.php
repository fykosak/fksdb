<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniSubmit;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use Nette\Utils\Html;

/**
 * Class PointsRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PointsRow extends AbstractColumnFactory {

    public function getTitle(): string {
        return _('Points');
    }

    /**
     * @param AbstractModelSingle|ModelFyziklaniSubmit $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $el = Html::el('span');
        if (!\is_null($model->points)) {
            return $el->addText($model->points);
        }
        return $el->addAttributes(['class' => 'badge badge-warning'])->addText(_('revoked'));
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }
}
