<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

/**
 * Class DateTimeLocalInput
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DateTimeLocalInput extends AbstractDateInput {

    public function __construct(?string $label = null) {
        parent::__construct('datetime-local', 'Y-m-d\TH:i:s', $label);
    }
}
