<?php

namespace FKSDB\Components\DatabaseReflection\ColumnFactories;

use FKSDB\Components\DatabaseReflection\ValuePrinters\EmailPrinter;
use FKSDB\Components\DatabaseReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class EmailRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EmailColumnFactory extends DefaultColumnFactory {

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));
        return $control;
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new EmailPrinter())($model->{$this->getModelAccessKey()});
    }
}
