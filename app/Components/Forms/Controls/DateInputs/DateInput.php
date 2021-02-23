<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

/**
 * Class DateInput
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DateInput extends AbstractDateInput {

    public function __construct(?string $label = null) {
        parent::__construct('date', 'Y-m-d', $label);
    }
}
