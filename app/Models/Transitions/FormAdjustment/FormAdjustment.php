<?php

namespace FKSDB\Models\Transitions\FormAdjustment;

use FKSDB\Models\Transitions\Holder\ModelHolder;

interface FormAdjustment {

    public function adjust(array $values, ModelHolder $holder): array;
}
