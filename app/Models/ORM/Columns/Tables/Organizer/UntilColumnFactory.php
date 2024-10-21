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
class UntilColumnFactory extends ColumnFactory
{
    /**
     * @param OrganizerModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        if (\is_null($model->until)) {
            return Html::el('span')->addAttributes(['class' => 'badge bg-success'])->addText(_('Still organizes'));
        } else {
            return StringPrinter::getHtml((string)$model->until);
        }
    }

    protected function createFormControl(...$args): BaseControl
    {
        [$min, $max] = $args;
        if (\is_null($max) || \is_null($min)) {
            throw new \InvalidArgumentException();
        }
        $control = new TextInput($this->getTitle());

        $control->addCondition(Form::FILLED)
            ->addRule(Form::NUMERIC, _('Must be a number'))
            ->addRule(Form::RANGE, _('Final year is not in interval [%d, %d].'), [$min, $max]);
        return $control;
    }
}
