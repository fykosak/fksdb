<?php


namespace FKSDB\Components\Forms\Controls\DateInputs;


class WeekInput extends AbstractDateInput {
    protected function getFormat(): string {
        return 'Y-\WW';
    }

    protected function getType(): string {
        return 'week';
    }
}
