<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Teacher;

use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\Components\Forms\Controls\DateInputs\DateInput;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelTeacher;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class SinceRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Teacher
 */
class SinceRow extends AbstractTeacherRow {

    /**
     * @param AbstractModelSingle|ModelTeacher $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new DatePrinter('Y. m. d.'))($model->since);
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Since');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        return new DateInput($this->getTitle());
    }
}