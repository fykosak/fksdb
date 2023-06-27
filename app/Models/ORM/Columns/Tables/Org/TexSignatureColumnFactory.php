<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Org;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class TexSignatureColumnFactory extends ColumnFactory
{

    protected function createFormControl(...$args): BaseControl
    {
        $control = new TextInput($this->getTitle());

        $control->addRule(Form::MAX_LENGTH, _('Max length reached'), 32);
        $control->addCondition(Form::FILLED)
            ->addRule(
                Form::PATTERN,
                sprintf(_('%s contains forbidden characters.'), $this->getTitle()),
                '[a-z][a-z0-9._\-]*'
            );
        return $control;
    }
}
