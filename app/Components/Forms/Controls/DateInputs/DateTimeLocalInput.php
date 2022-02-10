<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\DateInputs;

class DateTimeLocalInput extends AbstractDateInput
{
    public function __construct(?string $label = null)
    {
        parent::__construct('datetime-local', 'Y-m-d\TH:i:s', $label);
    }
}
