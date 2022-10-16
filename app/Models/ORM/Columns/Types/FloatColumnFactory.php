<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\NumberPrinter;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

class FloatColumnFactory extends ColumnFactory
{
    private string $nullValue = 'notSet';

    private ?string $prefix = null;

    private ?string $suffix = null;

    private int $decimalDigitsCount;

    public function setDecimalDigitsCount(int $count): void
    {
        $this->decimalDigitsCount = $count;
    }

    protected function createHtmlValue(Model $model): Html
    {
        return (new NumberPrinter($this->prefix, $this->suffix, $this->decimalDigitsCount, $this->nullValue))(
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
        $control->addRule(Form::FLOAT, _('Must be a floating number'));
        return $control;
    }
}
