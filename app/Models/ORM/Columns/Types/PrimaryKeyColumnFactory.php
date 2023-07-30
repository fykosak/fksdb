<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ValuePrinters\StringPrinter;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<Model>
 */
class PrimaryKeyColumnFactory extends ColumnFactory
{
    protected function createHtmlValue(Model $model): Html
    {
        return (new StringPrinter())('#' . $model->getPrimary());
    }

    protected function createFormControl(...$args): BaseControl
    {
        throw new OmittedControlException();
    }
}
