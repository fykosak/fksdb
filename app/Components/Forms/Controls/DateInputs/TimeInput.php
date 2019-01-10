<?php


namespace FKSDB\Components\Forms\Controls\DateInputs;

class TimeInput extends AbstractDateInput {

    protected function getFormat(): string {
        return 'H:i:s';
    }

    protected function getType(): string {
        return 'time';
    }
}
