<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Org;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\EmailPrinter;
use FKSDB\Models\Exceptions\ContestNotFoundException;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelOrg;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

class DomainAliasColumnFactory extends ColumnFactory
{

    /**
     * @param ModelOrg $model
     * @throws ContestNotFoundException
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        switch ($model->contest_id) {
            case ModelContest::ID_FYKOS:
                return (new EmailPrinter())($model->domain_alias . '@fykos.cz');
            case ModelContest::ID_VYFUK:
                return (new EmailPrinter())($model->domain_alias . '@vyfuk.mff.cuni.cz');
            default:
                throw new ContestNotFoundException($model->contest_id);
        }
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::MAX_LENGTH, _('Max length reached'), 32);
        $control->addCondition(Form::FILLED);
        $control->addRule(
            Form::PATTERN,
            sprintf(_('%s contains forbidden characters.'), $this->getTitle()),
            '[a-z][a-z0-9._\-]*'
        );
        return $control;
    }
}
