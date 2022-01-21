<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\StoredQuery\StoredQuery;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Nette\Forms\Form;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;

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
