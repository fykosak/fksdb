<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Teacher;

use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\Components\Forms\Controls\DateInputs\DateInput;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelTeacher;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class UntilRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Teacher
 */
class UntilRow extends AbstractTeacherRow {

    /**
     * @param AbstractModelSingle|ModelTeacher $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if ($model->until === null) {
            return Html::el('span')->addAttributes(['class' => 'badge badge-success'])->addText(_('Still teaches'));
        }
        return (new DatePrinter(_('__date_format')))($model->until);
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Teaches until');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        return new DateInput($this->getTitle());
    }
}
