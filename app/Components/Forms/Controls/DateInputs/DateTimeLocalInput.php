<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

class DateTimeLocalInput extends AbstractDateInput
{
    /**
     * DateTimeLocalInput constructor.
     * @param null $label
     * @param null $maxLength
     */
    public function __construct($label = null, $maxLength = null)
    {
        parent::__construct('datetime-local', 'Y-m-d\TH:i:s', $label, $maxLength);
    }
}
