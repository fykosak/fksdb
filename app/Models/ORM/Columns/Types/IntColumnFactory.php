<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\NumberPrinter;
use Fykosak\NetteORM\AbstractModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

class IntColumnFactory extends ColumnFactory
{

    private string $nullValue = 'notSet';

    private ?string $prefix = null;

    private ?string $suffix = null;

    protected function createHtmlValue(AbstractModel $model): Html
    {
        return (new NumberPrinter($this->prefix, $this->suffix, 0, $this->nullValue))(
            $model->{$this->getModelAccessKey()}
        );
    }

    public function setNullValueFormat(string $nullValue): void
    {
        $this->nullValue = $nullValue;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function setSuffix(string $suffix): void
    {
        $this->suffix = $suffix;
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::NUMERIC);
        return $control;
    }
}
