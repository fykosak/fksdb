<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

class WeekInput extends AbstractDateInput {
    /**
     * WeekInput constructor.
     * @param null $label
     * @param null $maxLength
     */
    public function __construct($label = null, $maxLength = null) {
        parent::__construct('week', 'Y-\WW', $label, $maxLength);
    }
}
