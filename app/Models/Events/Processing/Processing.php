<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Processing;

interface Processing
{
    public function process(array $values): array;
}
