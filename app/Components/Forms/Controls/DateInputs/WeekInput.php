<?php


namespace FKSDB\Components\Forms\Controls\DateInputs;


/**
 * Class WeekInput
 * @package FKSDB\Components\Forms\Controls\DateInputs
 */
class WeekInput extends AbstractDateInput {
    /**
     * @return string
     */
    protected function getFormat(): string {
        return 'Y-\WW';
    }

    /**
     * @return string
     */
    protected function getType(): string {
        return 'week';
    }
}
