<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Grid;

use FKSDB\Models\Events\Model\Holder\Holder;

interface HolderSource
{
    /**
     * @return Holder[]
     */
    public function getHolders(): array;
}
