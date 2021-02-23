<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

/**
 * Class TimeInput
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TimeInput extends AbstractDateInput {

    public function __construct(?string $label = null) {
        parent::__construct('time', 'H:i:s', $label);
    }
}
