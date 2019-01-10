<?php


namespace FKSDB\Components\Forms\Controls\DateInputs;


class MonthInput extends AbstractDateInput {
    protected function getFormat(): string {
        return 'Y-m';
    }

    protected function getType(): string {
        return 'month';
    }
}
