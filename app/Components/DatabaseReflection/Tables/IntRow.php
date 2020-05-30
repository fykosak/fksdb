<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\DatabaseReflection\ValuePrinters\NumberPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

class IntRow extends DefaultRow {
    /** @var string */
    private $nullValue = 'notSet';
    /** @var string */
    private $prefix;
    /** @var string */
    private $suffix;

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new NumberPrinter($this->prefix, $this->suffix, 0, $this->nullValue))($model->{$this->getModelAccessKey()});
    }

    /**
     * @param string $nullValue
     * @return void
     */
    public function setNullValueFormat(string $nullValue) {
        $this->nullValue = $nullValue;
    }

    /**
     * @param string $prefix
     * @return void
     */
    public function setPrefix(string $prefix) {
        $this->prefix = $prefix;
    }

    /**
     * @param string $suffix
     * @return void
     */
    public function setSuffix(string $suffix) {
        $this->suffix = $suffix;
    }

    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::NUMERIC);
        return $control;
    }
}
