<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Processing;

use FKSDB\Models\Events\Machine\BaseMachine;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

interface Processing
{
    public function process(
        ?string $state,
        ArrayHash $values,
        BaseMachine $primaryMachine,
        BaseHolder $holder,
        Logger $logger,
        ?Form $form = null
    ): ?string;
}
