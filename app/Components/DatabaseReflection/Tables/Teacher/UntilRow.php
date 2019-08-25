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
        return (new DatePrinter('Y. m. d.'))($model->until);
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Until');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        return new DateInput($this->getTitle());
    }
}