<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\PersonInfoModel;
use FKSDB\Models\ValuePrinters\EmailPrinter;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

class EmailColumnFactory extends ColumnFactory
{

    protected function createFormControl(...$args): BaseControl
    {
        $control = new TextInput($this->getTitle());
        $control->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL, _('Invalid e-mail.'));
        return $control;
    }

    /**
     * @param PersonInfoModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return (new EmailPrinter())($model->email);
    }
}
