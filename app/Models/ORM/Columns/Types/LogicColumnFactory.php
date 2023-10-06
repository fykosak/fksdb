<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\UI\BooleanPrinter;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<TModel,ArgType>
 * @phpstan-template TModel of Model
 * @phpstan-template ArgType
 */
class LogicColumnFactory extends ColumnFactory
{
    protected function createHtmlValue(Model $model): Html
    {
        return BooleanPrinter::getHtml($model->{$this->modelAccessKey});
    }

    protected function createFormControl(...$args): BaseControl
    {
        return new Checkbox(_($this->getTitle()));
    }
}
