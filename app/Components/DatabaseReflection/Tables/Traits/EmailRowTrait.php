<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\DatabaseReflection\ValuePrinters\EmailPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Trait EmailRowTrait
 * @package FKSDB\Components\DatabaseReflection
 */
trait EmailRowTrait {

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));
        return $control;
    }

    /**
     * @param AbstractModelSingle $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new EmailPrinter)($model->{$this->getModelAccessKey()});
    }

    /**
     * @return string
     * only must exists
     */
    abstract function getTitle(): string;

    /**
     * @return string
     * only must exists
     */
    abstract function getModelAccessKey(): string;
}
