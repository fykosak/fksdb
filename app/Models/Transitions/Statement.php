<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

interface Statement
{
    public function __invoke($holder, ...$args);
}
