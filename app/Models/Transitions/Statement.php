<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

use FKSDB\Models\Transitions\Holder\ModelHolder;

interface Statement
{
    public function __invoke(ModelHolder $holder);
}
