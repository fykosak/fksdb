<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Tabor;

use FKSDB\Models\Email\Source\TransitionEmail;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends TransitionEmail<EventParticipantModel>
 */
class OrganizerMailCallback extends TransitionEmail
{
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'organizer.latte';
    }

    protected function getData(ModelHolder $holder): array
    {
        return [
            'topic' => EmailMessageTopic::from(EmailMessageTopic::Contest),
            'lang' => Language::from(Language::CS),
            'sender' => 'Výfuk <vyfuk@vyfuk.org>',
        ];
    }

    /**
     * @phpstan-param ParticipantHolder|Transition<ParticipantHolder> $args
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    public function __invoke(...$args): void
    {
        /**
         * @phpstan-var ParticipantHolder $holder
         * @phpstan-var Transition<ParticipantHolder> $transition
         */
        [$holder, $transition] = $args;
        foreach ($this->getPersons($holder) as $person) { //@phpstan-ignore-line
            $data = array_merge(
                $this->getData($holder), //@phpstan-ignore-line
                $this->createMessageText($holder, $transition, $person) //@phpstan-ignore-line
            );
            $data['recipient'] = 'Výfučí přihlášky <vyfuk-prihlasky@vyfuk.org>';
            $this->emailMessageService->addMessageToSend($data);
        }
    }
}
