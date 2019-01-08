<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

class DateInput extends AbstractDateInput {

    protected function getFormat(): string {
        return 'Y-m-d';
    }

    protected function getType(): string {
        return 'date';
    }
}
