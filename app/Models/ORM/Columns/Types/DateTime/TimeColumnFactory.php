<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types\DateTime;

use FKSDB\Components\Forms\Controls\DateInputs\TimeInput;
use Nette\Forms\Controls\BaseControl;

class TimeColumnFactory extends AbstractDateTimeColumnFactory
{

    protected function createFormControl(...$args): BaseControl
    {
        return new TimeInput($this->getTitle());
    }

    protected function getDefaultFormat(): string
    {
        return _('__time');
    }
}
