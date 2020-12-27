<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Types;

use FKSDB\Models\ValuePrinters\EmailPrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class EmailRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EmailColumnFactory extends DefaultColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL, _('Invalid e-mail.'));
        return $control;
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new EmailPrinter())($model->{$this->getModelAccessKey()});
    }
}
