<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\StoredQuery\StoredQuery;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

/**
 * @phpstan-extends ColumnFactory<QueryModel,never>
 */
class NameColumnFactory extends ColumnFactory
{

    protected function createFormControl(...$args): BaseControl
    {
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::FILLED, _('The query name must be filled in.'))
            ->addRule(Form::MAX_LENGTH, _('The query name is too long.'), 32);
        return $control;
    }
}
