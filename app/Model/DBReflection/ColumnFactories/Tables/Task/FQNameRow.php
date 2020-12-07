<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Tables\Task;

use FKSDB\Model\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Model\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\ModelTask;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class FQNameRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FQNameRow extends DefaultColumnFactory {

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
