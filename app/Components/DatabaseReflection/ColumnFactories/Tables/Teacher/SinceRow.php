<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Teacher;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\Components\Forms\Controls\DateInputs\DateInput;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelTeacher;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class SinceRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SinceRow extends AbstractColumnFactory {

    /**
     * @param AbstractModelSingle|ModelTeacher $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if ($model->since === null) {
            return Html::el('span')->addAttributes(['class' => 'badge badge-secondary'])->addText(_('undefined'));
        }
        return (new DatePrinter(_('__date_format')))($model->since);
    }

    public function getTitle(): string {
        return _('Teaches since');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        return new DateInput($this->getTitle());
    }
    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }
}
