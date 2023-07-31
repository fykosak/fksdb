<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Org;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ValuePrinters\EmailPrinter;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Http\IResponse;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<OrgModel>
 */
class DomainAliasColumnFactory extends ColumnFactory
{
    /**
     * @param OrgModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        switch ($model->contest_id) {
            case ContestModel::ID_FYKOS:
                return (new EmailPrinter())($model->domain_alias . '@fykos.cz');
            case ContestModel::ID_VYFUK:
                return (new EmailPrinter())($model->domain_alias . '@vyfuk.mff.cuni.cz');
            default:
                throw new \InvalidArgumentException(
                    sprintf(_('Contest %d not found'), $model->contest_id),
                    IResponse::S404_NOT_FOUND
                );
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
