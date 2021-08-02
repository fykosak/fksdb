<?php

namespace FKSDB\Models\ORM\Columns\Tables\Task;

use FKSDB\Models\ORM\Columns\AbstractColumnException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\ModelTask;
use Fykosak\NetteORM\AbstractModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

class FQNameColumnFactory extends ColumnFactory
{

    /**
     * @param AbstractModel|ModelTask $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        return Html::el('span')->addText($model->getFQName());
    }

    /**
     * @param ...$args
     * @return BaseControl
     * @throws AbstractColumnException
     */
    protected function createFormControl(...$args): BaseControl
    {
        throw new AbstractColumnException();
    }
}
