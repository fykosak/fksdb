<?php

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Models\ORM\Columns\Types\DefaultColumnFactory;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelPersonInfo;
use FKSDB\Models\ValuePrinters\EmailPrinter;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class EmailRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EmailRow extends DefaultColumnFactory {

    /**
     * @param array $args
     * @return BaseControl
     */
    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL, _('Invalid e-mail.'));
        return $control;
    }

    /**
     * @param AbstractModelSingle|ModelPersonInfo $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new EmailPrinter())($model->email);
    }
}
