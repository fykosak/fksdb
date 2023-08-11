<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnlyInput;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\PersonInfoModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * @phpstan-extends ColumnFactory<PersonInfoModel,never>
 */
class IsicNumberColumnFactory extends ColumnFactory
{
    protected function createFormControl(...$args): BaseControl
    {
        $control = new WriteOnlyInput($this->getTitle());
        $control->addFilter(fn($value) => str_replace(' ', '', $value))
            ->addRule(Form::LENGTH, _('ISIC must start and end with a capital letter and contain 12 digits'), 14)
            ->addRule(
                Form::PATTERN,
                _('ISIC must start and end with a capital letter and contain 12 digits'),
                '^[A-Z]\d{12}[A-Z]$'
            );
        return $control;
    }
}
