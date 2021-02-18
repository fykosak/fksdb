<?php

namespace FKSDB\Models\ORM\Columns\Tables\Task;

use FKSDB\Models\ORM\Columns\AbstractColumnException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelTask;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class FQNameRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FQNameRow extends ColumnFactory {

    /**
     * @param AbstractModelSingle|ModelTask $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->getFQName());
    }

    /**
     * @param ...$args
     * @return BaseControl
     * @throws AbstractColumnException
     */
    protected function createFormControl(...$args): BaseControl {
        throw new AbstractColumnException();
    }
}
