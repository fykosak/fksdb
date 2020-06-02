<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;
/**
 * Class DateTimeLocalInput
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DateTimeLocalInput extends AbstractDateInput {

    protected function getFormat(): string {
        return 'Y-m-d\TH:i:s';
    }

    protected function getType(): string {
        return 'datetime-local';
    }
}
