<?php

declare(strict_types=1);

namespace FKSDB\Models\Email;

use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Models\EmailMessageState;
use FKSDB\Models\Transitions\Machine\EmailMachine;
use FKSDB\Models\Transitions\TransitionsMachineFactory;

final class SenderFactory
{
    private EmailMachine $machine;

    public function __construct(
        TransitionsMachineFactory $transitionsMachineFactory
    ) {
        $this->machine = $transitionsMachineFactory->getEmailMachine();
    }

    /**
     * @throws \Throwable
     */
    public function send(EmailMessageModel $model): void
    {
        $holder = $this->machine->createHolder($model);
        $transition = $this->machine->getTransitions()
            ->filterByTarget(EmailMessageState::from(EmailMessageState::Sent))
            ->filterAvailable($holder)
            ->select();
        $transition->execute($holder);
    }
}
