<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

/**
 * Class TimeInput
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TimeInput extends AbstractDateInput {

    protected function getFormat(): string {
        return 'H:i:s';
    }

    protected function getType(): string {
        return 'time';
    }
}
