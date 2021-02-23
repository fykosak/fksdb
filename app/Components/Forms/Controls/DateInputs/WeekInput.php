<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

/**
 * Class WeekInput
 * @author Michal Červeňák <miso@fykos.cz>
 */
class WeekInput extends AbstractDateInput {

    public function __construct(?string $label = null) {
        parent::__construct('week', 'Y-\WW', $label);
    }
}
