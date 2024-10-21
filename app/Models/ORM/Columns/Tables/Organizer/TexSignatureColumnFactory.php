<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Organizer;

use FKSDB\Models\ORM\Columns\Types\StringColumnFactory;
use FKSDB\Models\ORM\Models\OrganizerModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * @phpstan-extends StringColumnFactory<OrganizerModel,never>
 */
class TexSignatureColumnFactory extends StringColumnFactory
{
    protected function createFormControl(...$args): BaseControl
    {
        $control = parent::createFormControl(...$args);

        $control->addCondition(Form::FILLED)
            ->addRule(
                Form::PATTERN,
                sprintf(_('%s contains forbidden characters.'), $this->getTitle()),
                '[a-z][a-z0-9._\-]*'
            );
        return $control;
    }
}
