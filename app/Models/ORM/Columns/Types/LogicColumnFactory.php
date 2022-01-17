<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\BinaryPrinter;
use Fykosak\NetteORM\AbstractModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Utils\Html;

class LogicColumnFactory extends ColumnFactory
{
    protected function createHtmlValue(AbstractModel $model): Html
    {
        return (new BinaryPrinter())($model->{$this->getModelAccessKey()});
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = new Checkbox(_($this->getTitle()));

        // if (!$this->metaData['nullable']) {
        // $control->setRequired();
        //  }
        $description = $this->getDescription();
        if ($description) {
            $control->setOption('description', $this->getDescription());
        }
        return $control;
    }
}
