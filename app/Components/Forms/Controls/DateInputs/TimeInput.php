<?php


namespace FKSDB\Components\Forms\Controls\DateInputs;

class TimeInput extends AbstractDateInput {

    protected function getFormat() {
        return 'H:i:s';
    }

    protected function getType() {
        return 'time';
    }
}
