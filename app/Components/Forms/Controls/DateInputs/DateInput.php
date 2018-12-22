<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

class DateInput extends AbstractDateInput {

    protected function getFormat() {
        return 'Y-m-d';
    }

    protected function getType() {
        return 'date';
    }
}
