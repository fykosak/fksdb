<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail;

use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\PersonService;

class ResolverFactory
{
    private PersonService $personService;
    private EmailMessageService $emailMessageService;

    public function inject(PersonService $personService, EmailMessageService $emailMessageService): void
    {
        $this->personService = $personService;
        $this->emailMessageService = $emailMessageService;
    }

    public function resolve(EmailMessageModel $model): void
    {
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
