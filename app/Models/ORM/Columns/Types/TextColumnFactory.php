<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\StringPrinter;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<Model,never>
 */
class TextColumnFactory extends ColumnFactory
{

    protected function createHtmlValue(Model $model): Html
    {
        return (new StringPrinter())($model->{$this->modelAccessKey});
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = new TextArea(_($this->getTitle()));
        $description = $this->getDescription();
        if ($description) {
            $control->setOption('description', $this->getDescription());
        }
        return $control;
    }
}
