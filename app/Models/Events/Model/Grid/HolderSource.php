<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Grid;

use FKSDB\Models\Events\Model\Holder\BaseHolder;

interface HolderSource
{
    /**
     * @return BaseHolder[]
     */
    public function getHolders(): array;
}
