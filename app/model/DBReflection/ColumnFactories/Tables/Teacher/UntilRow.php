<?php

namespace FKSDB\DBReflection\ColumnFactories\Tables\Teacher;

use FKSDB\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\ValuePrinters\DatePrinter;
use FKSDB\Components\Forms\Controls\DateInputs\DateInput;
use FKSDB\ORM\Models\AbstractModelSingle;
use FKSDB\ORM\Models\ModelTeacher;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class UntilRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class UntilRow extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelTeacher $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if ($model->until === null) {
            return Html::el('span')->addAttributes(['class' => 'badge badge-success'])->addText(_('Still teaches'));
        }
        return (new DatePrinter(_('__date')))($model->until);
    }

    protected function createFormControl(...$args): BaseControl {
        return new DateInput($this->getTitle());
    }
}
