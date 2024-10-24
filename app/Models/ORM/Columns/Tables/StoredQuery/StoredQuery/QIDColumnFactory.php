<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\StoredQuery\StoredQuery;

use FKSDB\Models\ORM\Columns\Types\StringColumnFactory;
use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * @phpstan-extends StringColumnFactory<QueryModel,never>
 */
class QIDColumnFactory extends StringColumnFactory
{
    protected function createFormControl(...$args): BaseControl
    {
        $control = parent::createFormControl(...$args);
        $control->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, _('The query name is too long'), 64)
            ->addRule(Form::PATTERN, _('QID can contain only english letters, numbers and dots.'), '[a-z][a-z0-9.]*');
        return $control;
    }
}
