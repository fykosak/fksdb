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
class NameColumnFactory extends StringColumnFactory
{
    protected function createFormControl(...$args): BaseControl
    {
        $control = parent::createFormControl(...$args);
        $control->addRule(Form::FILLED, _('The query name must be filled in.'))
            ->addRule(Form::MAX_LENGTH, _('The query name is too long.'), 32);
        return $control;
    }
}
