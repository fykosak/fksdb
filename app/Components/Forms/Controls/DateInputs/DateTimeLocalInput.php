<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

class DateTimeLocalInput extends AbstractDateInput {

    protected function getFormat() {
        return 'Y-m-d\TH:i:s';
    }

    protected function getType() {
        return 'datetime-local';
    }
}
