<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Processing;

use Nette\Utils\ArrayHash;

interface Processing
{
    /**
     * @param ArrayHash<ArrayHash<mixed>> $values
     */
    public function process(ArrayHash $values): void;
}
