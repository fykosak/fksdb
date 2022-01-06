<?php

declare(strict_types=1);

namespace FKSDB\Models;

interface ArrayAble {
    public function __toArray(): array;
}
