<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\DateInputs;

class WeekInput extends AbstractDateInput
{
    public function __construct(?string $label = null)
    {
        parent::__construct('week', 'Y-\WW', $label);
    }
}
