<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms;

use FKSDB\Models\Transitions\Holder\ModelHolder;

interface FormAdjustment
{
    public function adjust(array $values, ModelHolder $holder): void;
}
