<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\Model\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-template TModel of Model
 * @phpstan-template ArgType
 * @phpstan-extends ColumnFactory<TModel,ArgType>
 */
class IntColumnFactory extends ColumnFactory
{
    use NumberFactoryTrait;

    protected function createHtmlValue(Model $model): Html
    {
        return ($this->printer)((int)$model->{$this->modelAccessKey});
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::NUMERIC, _('Must be a numeric'));
        return $control;
    }
}
