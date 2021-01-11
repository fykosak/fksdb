<?php

namespace FKSDB\Models\ORM\Columns\ColumnFactories\Tables\Task;

use FKSDB\Models\ORM\Columns\ColumnFactories\AbstractColumnException;
use FKSDB\Models\ORM\Columns\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelTask;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class FQNameRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FQNameColumnFactory extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelTask $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->getFQName());
    }

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws AbstractColumnException
     */
    protected function createFormControl(...$args): BaseControl {
        throw new AbstractColumnException();
    }
}
