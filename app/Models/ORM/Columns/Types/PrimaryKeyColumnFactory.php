<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\UI\StringPrinter;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<Model,never>
 */
class PrimaryKeyColumnFactory extends ColumnFactory
{
    protected function createHtmlValue(Model $model): Html
    {
        return StringPrinter::getHtml('#' . $model->getPrimary());
    }

    protected function createFormControl(...$args): BaseControl
    {
        throw new OmittedControlException();
    }
}
