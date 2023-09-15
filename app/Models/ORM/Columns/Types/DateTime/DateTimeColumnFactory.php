<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types\DateTime;

use FKSDB\Components\Forms\Controls\DateInputs\DateTimeLocalInput;
use Nette\Forms\Controls\BaseControl;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 * @phpstan-template ArgType
 * @phpstan-extends AbstractDateTimeColumnFactory<TModel,ArgType>
 */
class DateTimeColumnFactory extends AbstractDateTimeColumnFactory
{
    protected function createFormControl(...$args): BaseControl
    {
        return new DateTimeLocalInput($this->getTitle());
    }

    protected function getDefaultFormat(): string
    {
        return _('__date_time');
    }
}
