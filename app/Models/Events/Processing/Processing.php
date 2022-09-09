<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Processing;

use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

interface Processing
{
    public function process(
        ?EventParticipantStatus $state,
        ArrayHash $values,
        AbstractMachine $machine,
        ModelHolder $holder,
        Logger $logger,
        ?Form $form = null
    ): ?EventParticipantStatus;
}
