<?php


namespace FKSDB\Components\Forms\Controls\DateInputs;


class MonthInput extends AbstractDateInput {
    protected function getFormat() {
        return 'Y-m';
    }

    protected function getType() {
        return 'month';
    }
}
