<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

class TimeInput extends AbstractDateInput {
    /**
     * TimeInput constructor.
     * @param null $label
     * @param null $maxLength
     */
    public function __construct($label = null, $maxLength = null) {
        parent::__construct('time', 'H:i:s', $label, $maxLength);
    }
}
