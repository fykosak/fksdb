<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Person;

use FKSDB\Models\ORM\Columns\AbstractColumnException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\StringPrinter;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

class FullNameColumnFactory extends ColumnFactory
{

    /**
     * @throws AbstractColumnException
     */
    protected function createFormControl(...$args): BaseControl
    {
        throw new AbstractColumnException();
    }

    /**
     * @param PersonModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return (new StringPrinter())($model->getFullName());
    }
}
