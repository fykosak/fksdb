<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\DateInputs;

class WeekInput extends AbstractDateInput {
    /**
     * WeekInput constructor.
     * @param object|string $label
     */
    public function __construct($label = null, int $maxLength = null) {
        parent::__construct('week', 'Y-\WW', $label, $maxLength);
    }
}
