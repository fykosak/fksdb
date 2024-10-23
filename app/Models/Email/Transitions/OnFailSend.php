<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Transitions;

use FKSDB\Models\ORM\Models\EmailMessageState;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\Exceptions\RejectedEmailException;
use FKSDB\Models\Transitions\FailHandler;
use FKSDB\Models\Transitions\Holder\EmailHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use Tracy\Debugger;

/**
 * @phpstan-implements FailHandler<EmailHolder>
 */
final class OnFailSend implements FailHandler
{
    private EmailMessageService $emailMessageService;

    public function __construct(EmailMessageService $emailMessageService)
    {
        $this->emailMessageService = $emailMessageService;
    }

    public function handle(\Throwable $exception, ModelHolder $holder, Transition $transition): void
    {
        $model = $holder->getModel();
        if ($exception instanceof RejectedEmailException) {
            $this->emailMessageService->storeModel(['state' => EmailMessageState::Rejected->value], $model);
            Debugger::log($exception, 'mailer-exceptions-unsubscribed');
        } else {
            $this->emailMessageService->storeModel(['state' => EmailMessageState::Failed->value], $model);
            Debugger::log($exception, 'mailer-exceptions');
        }
    }
}
