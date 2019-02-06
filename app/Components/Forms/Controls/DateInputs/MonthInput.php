<?php


namespace FKSDB\Components\Forms\Controls\DateInputs;


/**
 * Class MonthInput
 * @package FKSDB\Components\Forms\Controls\DateInputs
 */
class MonthInput extends AbstractDateInput {
    /**
     * @return string
     */
    protected function getFormat(): string {
        return 'Y-m';
    }

    /**
     * @return string
     */
    protected function getType(): string {
        return 'month';
    }
}
