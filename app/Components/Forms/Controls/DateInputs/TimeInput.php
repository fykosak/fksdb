<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

class TimeInput extends AbstractDateInput {
    /**
     * TimeInput constructor.
     * @param object|string|null $label
     */
    public function __construct($label = null,int $maxLength = null) {
        parent::__construct('time', 'H:i:s', $label, $maxLength);
    }
}
