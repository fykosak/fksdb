<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Transitions;

use FKSDB\Models\ORM\Models\PersonEmailPreferenceModel;
use FKSDB\Models\ORM\Services\Exceptions\RejectedEmailException;
use FKSDB\Models\ORM\Services\UnsubscribedEmailService;
use FKSDB\Models\Transitions\Holder\EmailHolder;
use FKSDB\Models\Transitions\Statement;

/**
 * @phpstan-implements Statement<void,EmailHolder>
 * @note check if is any account associated with this email, and change recipient to person
 */
final class CheckPreference implements Statement
{
    private UnsubscribedEmailService $unsubscribedEmailService;

    public function __construct(
        UnsubscribedEmailService $unsubscribedEmailService
    ) {
        $this->unsubscribedEmailService = $unsubscribedEmailService;
    }

    /**
     * @throws RejectedEmailException
     */
    public function __invoke(...$args): void
    {
        /** @var EmailHolder $holder */
        [$holder] = $args;
        $model = $holder->getModel();
        if (isset($model->recipient_person_id)) {
            $preferenceType = $model->topic->mapToPreference();
            if ($preferenceType) {
                /** @var PersonEmailPreferenceModel|null $preference */
                $preference = $model->person->getEmailPreferences()->where('option', $preferenceType)->fetch();
                if ($preference && !$preference->value) {
                    throw new RejectedEmailException();
                }
            }
        } else {
            // check if email is not in unsubscribed
            $row = $this->unsubscribedEmailService->getTable()
                ->where('email_hash = SHA1(?)', $model->recipient)
                ->fetch();
            if ($row) {
                throw new RejectedEmailException();
            }
        }
    }
}
