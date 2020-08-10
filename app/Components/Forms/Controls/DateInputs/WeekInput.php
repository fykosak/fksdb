<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

/**
 * Class WeekInput
 * @author Michal Červeňák <miso@fykos.cz>
 */
class WeekInput extends AbstractDateInput {

    protected function getFormat(): string {
        return 'Y-\WW';
    }

    protected function getType(): string {
        return 'week';
    }
}
