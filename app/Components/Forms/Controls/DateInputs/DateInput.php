<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

/**
 * Class DateInput
 * @package FKSDB\Components\Forms\Controls\DateInputs
 */
class DateInput extends AbstractDateInput {

    /**
     * @return string
     */
    protected function getFormat(): string {
        return 'Y-m-d';
    }

    /**
     * @return string
     */
    protected function getType(): string {
        return 'date';
    }
}
