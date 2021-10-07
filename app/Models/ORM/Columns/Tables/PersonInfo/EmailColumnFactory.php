<?php

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelPersonInfo;
use FKSDB\Models\ValuePrinters\EmailPrinter;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

class EmailColumnFactory extends ColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL, _('Invalid e-mail.'));
        return $control;
    }

    /**
     * @param AbstractModel|ModelPersonInfo $model
     */
    protected function createHtmlValue(AbstractModel $model): Html {
        return (new EmailPrinter())($model->email);
    }
}
