<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

/**
 * Class MonthInput
 * @author Michal Červeňák <miso@fykos.cz>
 */
class MonthInput extends AbstractDateInput {

    public function __construct(?string $label = null) {
        parent::__construct('month', 'Y-m', $label);
    }
}
