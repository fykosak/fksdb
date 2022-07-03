<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\StringPrinter;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnlyInput;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\Html;

class IdNumberColumnFactory extends ColumnFactory
{
    protected function createFormControl(...$args): BaseControl
    {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->addRule(Form::MAX_LENGTH, _('Max length reached'), 32);
        return $control;
    }

    protected function createHtmlValue(Model $model): Html
    {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
