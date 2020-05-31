<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\DatabaseReflection\ValuePrinters\NumberPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class IntRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class IntRow extends DefaultRow {

    private string $nullValue = 'notSet';

    private ?string $prefix = null;

    private ?string $suffix = null;

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new NumberPrinter($this->prefix, $this->suffix, 0, $this->nullValue))($model->{$this->getModelAccessKey()});
    }

    public function setNullValueFormat(string $nullValue): void {
        $this->nullValue = $nullValue;
    }

    public function setPrefix(string $prefix): void {
        $this->prefix = $prefix;
    }

    public function setSuffix(string $suffix): void {
        $this->suffix = $suffix;
    }

    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::NUMERIC);
        return $control;
    }
}
