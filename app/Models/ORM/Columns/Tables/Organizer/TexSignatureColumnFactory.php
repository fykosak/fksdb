<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Organizer;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\OrganizerModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

/**
 * @phpstan-extends ColumnFactory<OrganizerModel,never>
 */
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
