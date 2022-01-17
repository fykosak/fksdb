<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Transitions\Holder\ModelHolder;

interface TransitionCallback
{
    public function __invoke(ModelHolder $holder, ...$args): void;
}
