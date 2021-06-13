<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

class MonthInput extends AbstractDateInput {
    /**
     * MonthInput constructor.
     * @param null $label
     * @param null $maxLength
     */
    public function __construct($label = null, $maxLength = null) {
        parent::__construct('month', 'Y-m', $label, $maxLength);
    }
}
