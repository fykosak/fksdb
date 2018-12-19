<?php


namespace FKSDB\Components\Forms\Controls\DateInputs;


class WeekInput extends AbstractDateInput {
    protected function getFormat() {
        return 'Y-\WW';
    }

    protected function getType() {
        return 'week';
    }
}
