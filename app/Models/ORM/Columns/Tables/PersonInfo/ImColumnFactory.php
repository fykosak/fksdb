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
class ImColumnFactory extends ColumnFactory
{
    protected function createFormControl(...$args): BaseControl
    {
        $control = new  WriteOnlyInput($this->getTitle());
        $control->addRule(Form::MAX_LENGTH, _('Max length reached'), 32);
        return $control;
    }
}
