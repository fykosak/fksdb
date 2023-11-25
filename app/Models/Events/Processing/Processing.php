<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Processing;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

interface Processing
{
    /**
     * @phpstan-param ArrayHash<ArrayHash<mixed>> $values
     */
    public function process(ArrayHash $values): void;
}
