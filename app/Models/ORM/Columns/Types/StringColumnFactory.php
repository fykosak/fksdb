<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\StringPrinter;
use Fykosak\NetteORM\AbstractModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

class StringColumnFactory extends ColumnFactory
{

    protected function createHtmlValue(AbstractModel $model): Html
    {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = new TextInput(_($this->getTitle()));
        if ($this->getMetaData()['size']) {
            $control->addRule(Form::MAX_LENGTH, null, $this->getMetaData()['size']);
        }

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
