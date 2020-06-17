<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

/**
 * Class MonthInput
 * @author Michal Červeňák <miso@fykos.cz>
 */
class MonthInput extends AbstractDateInput {

    protected function getFormat(): string {
        return 'Y-m';
    }

    protected function getType(): string {
        return 'month';
    }
}
