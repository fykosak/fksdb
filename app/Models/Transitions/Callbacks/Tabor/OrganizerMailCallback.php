<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Tabor;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;

/**
 * @phpstan-extends MailCallback<BaseHolder>
 */
class OrganizerMailCallback extends MailCallback
{
    /**
     * @param BaseHolder $holder
     * @phpstan-param Transition<BaseHolder> $transition
     */
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'organizer.latte';
    }

    /**
     * @phpstan-return array{
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder): array
    {
        return [
            'sender' => 'Výfuk <vyfuk@vyfuk.org>',
        ];
    }

    /**
     * @phpstan-param BaseHolder|Transition<BaseHolder> $args
     * @throws BadTypeException
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
