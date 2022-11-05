<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Processing;

use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

interface Processing
{
    public function process(
        ?EventParticipantStatus $state,
        ArrayHash $values,
        ModelHolder $holder,
        Logger $logger,
        ?Form $form
    ): void;
}
