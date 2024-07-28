<?php

declare(strict_types=1);

namespace FKSDB\Models\Email;

use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Models\EmailMessageState;
use FKSDB\Models\Transitions\Machine\EmailMachine;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\TransitionsMachineFactory;

final class SenderFactory
{
    private EmailMachine $machine;

    public function __construct(
        TransitionsMachineFactory $transitionsMachineFactory
    ) {
        $this->machine = $transitionsMachineFactory->getEmailMachine();
    }

    public function send(EmailMessageModel $model): void
    {
        $holder = $this->machine->createHolder($model);
        $transition = Machine::selectTransition(
            Machine::filterByTarget(
                Machine::filterAvailable(
                    $this->machine->transitions,
                    $holder
                ),
                EmailMessageState::from(EmailMessageState::Sent)
            )
        );
        $this->machine->execute($transition, $holder);
    }
}
