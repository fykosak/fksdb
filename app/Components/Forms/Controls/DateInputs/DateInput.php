<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\DateInputs;

class DateInput extends AbstractDateInput
{
    public function __construct(?string $label = null)
    {
        parent::__construct('date', 'Y-m-d', $label);
    }
}
