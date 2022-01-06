<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\DateInputs;

class MonthInput extends AbstractDateInput {
    /**
     * MonthInput constructor.
     * @param null $label
     */
    public function __construct($label = null, int $maxLength = null) {
        parent::__construct('month', 'Y-m', $label, $maxLength);
    }
}
