<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\StoredQuery\StoredQuery;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;

class QIDColumnFactory extends ColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->setOption('description', _('The queries with QID cannot be deleted and QID can be used for permissions and permanent reference.'))
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, _('The query name is too long'), 64)
            ->addRule(Form::PATTERN, _('QID can contain only english letters, numbers and dots.'), '[a-z][a-z0-9.]*');
        return $control;
    }
}
