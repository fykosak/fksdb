<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Organizer;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\UI\StringPrinter;
use Fykosak\NetteORM\Model\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<OrganizerModel,int|null|never>
 */
class SinceColumnFactory extends ColumnFactory
{

    protected function createFormControl(...$args): BaseControl
    {
        [$min, $max] = $args;
        if (\is_null($max) || \is_null($min)) {
            throw new \InvalidArgumentException();
        }
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::NUMERIC, _('Must be a number'));
        $control->addRule(Form::FILLED);
        $control->addRule(Form::RANGE, _('First year is not in interval [%d, %d].'), [$min, $max]);
        return $control;
    }

    /**
     * @param OrganizerModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return StringPrinter::getHtml((string)$model->since);
    }
}
