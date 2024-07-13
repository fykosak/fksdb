<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Setkani;

use FKSDB\Models\Email\TransitionEmail;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends TransitionEmail<BaseHolder>
 */
class OrganizerTransitionMail extends TransitionEmail
{
    /**
     * @param BaseHolder $holder
     * @phpstan-param Transition<BaseHolder> $transition
     */
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'organizer.latte';
    }
    protected function getData(ModelHolder $holder): array
    {
        return [
            'sender' => 'Výfuk <vyfuk@vyfuk.org>',
            'topic' => EmailMessageTopic::from(EmailMessageTopic::Internal),
            'lang' => Language::from(Language::CS),
        ];
    }

    /**
     * @phpstan-param BaseHolder|Transition<BaseHolder> $args
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    public function __invoke(...$args): void
    {
        /**
         * @phpstan-var BaseHolder $holder
         * @phpstan-var Transition<BaseHolder> $transition
         */
        [$holder, $transition] = $args;
        foreach ($this->getPersons($holder) as $person) {
            $data = array_merge(
                $this->getData($holder),
                $this->createMessageText($holder, $transition, $person)
            );
            $data['recipient'] = 'Výfučí přihlášky <vyfuk-prihlasky@vyfuk.org>';
            $this->emailMessageService->addMessageToSend($data);
        }
    }
}
