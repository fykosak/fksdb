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
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));
        return $control;
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new EmailPrinter())($model->{$this->getModelAccessKey()});
    }

    /**
     * @return string
     * only must exists
     */
    abstract public function getTitle(): string;

    /**
     * @return string
     * only must exists
     */
    abstract public function getModelAccessKey(): string;
}
