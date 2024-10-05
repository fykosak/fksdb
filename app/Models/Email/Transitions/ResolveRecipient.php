<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Transitions;

use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\Transitions\Holder\EmailHolder;
use FKSDB\Models\Transitions\Statement;

/**
 * @phpstan-implements Statement<void,EmailHolder>
 */
final class ResolveRecipient implements Statement
{
    private PersonService $personService;
    private EmailMessageService $emailMessageService;

    public function __construct(PersonService $personService, EmailMessageService $emailMessageService)
    {
        $this->personService = $personService;
        $this->emailMessageService = $emailMessageService;
    }

    public function __invoke(...$args): void
    {
        /** @var EmailHolder $holder */
        [$holder] = $args;
        $model = $holder->getModel();
        if ($model->recipient) {
            $person = $this->personService->findByEmail($model->recipient);
            if ($person) {
                $this->emailMessageService->storeModel([
                    'recipient_person_id' => $person->person_id,
                    'recipient' => null,
                ], $model);
            }
        }
    }
}
