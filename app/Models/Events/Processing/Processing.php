<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Processing;

use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Model\Holder\Holder;
use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

interface Processing
{
    public function process(
        ?string $state,
        ArrayHash $values,
        Machine $machine,
        Holder $holder,
        Logger $logger,
        ?Form $form = null
    ): ?string;
}
