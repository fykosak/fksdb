<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\DateInputs;

class TimeInput extends AbstractDateInput
{
    public function __construct(?string $label = null)
    {
        parent::__construct('time', 'H:i:s', $label);
    }
}
