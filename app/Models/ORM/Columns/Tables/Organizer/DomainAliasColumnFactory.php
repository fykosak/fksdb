<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Organizer;

use FKSDB\Models\ORM\Columns\Types\StringColumnFactory;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\UI\EmailPrinter;
use Fykosak\NetteORM\Model\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Http\IResponse;
use Nette\Utils\Html;

/**
 * @phpstan-extends StringColumnFactory<OrganizerModel,never>
 */
class DomainAliasColumnFactory extends StringColumnFactory
{
    /**
     * @param OrganizerModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        switch ($model->contest_id) {
            case ContestModel::ID_FYKOS:
                return EmailPrinter::getHtml($model->domain_alias . '@fykos.cz');
            case ContestModel::ID_VYFUK:
                return EmailPrinter::getHtml($model->domain_alias . '@vyfuk.org');
            default:
                throw new \InvalidArgumentException(
                    sprintf(_('Contest %d not found'), $model->contest_id),
                    IResponse::S404_NOT_FOUND
                );
        }
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = parent::createFormControl(...$args);
        $control->addCondition(Form::FILLED);
        $control->addRule(
            Form::PATTERN,
            sprintf(_('%s contains forbidden characters.'), $this->getTitle()),
            '[a-z][a-z0-9._\-]*'
        );
        return $control;
    }
}
