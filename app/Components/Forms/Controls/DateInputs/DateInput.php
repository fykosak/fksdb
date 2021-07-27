<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

class DateInput extends AbstractDateInput {
    /**
     * DateInput constructor.
     * @param null $label
     * @param null $maxLength
     */
    public function __construct($label = null, $maxLength = null) {
        parent::__construct('date', 'Y-m-d', $label, $maxLength);
    }
}
