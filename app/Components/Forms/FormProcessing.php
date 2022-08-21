<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms;

use FKSDB\Models\Transitions\Holder\ModelHolder;

interface FormProcessing
{
    public function process(array $values, ModelHolder $holder): array;
}
