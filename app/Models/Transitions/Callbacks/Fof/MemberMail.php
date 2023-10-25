<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Fof;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Models\Transitions\Transition\Transition;

class MemberMail extends \FKSDB\Models\Transitions\Callbacks\TeamMemberMailCallback
{
    /**
     * @param TeamHolder $holder
     * @phpstan-param Transition<TeamHolder> $transition
     */
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'member.' .
            $transition->source->value . '.' .
            $transition->target->value . '.latte';
    }

    /**
     * @param TeamHolder $holder
     * @phpstan-param Transition<TeamHolder> $transition
     * @phpstan-return array{
     *     blind_carbon_copy:string|null,
     *     subject:string,
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder, Transition $transition): array
    {
        if ($holder->getModel()->game_lang->value === 'cs') {
            $subject = 'Registrace na Fyziklání – ' . $holder->getModel()->name;
            $sender = 'Fyziklání <fyziklani@fykos.cz>';
        } else {
            $subject = 'Fyziklani Registration – ' . $holder->getModel()->name;
            $sender = 'Fyziklani <fyziklani@fykos.cz>';
        }
        return [
            'subject' => $subject,
            'blind_carbon_copy' => 'FYKOS <fyziklani@fykos.cz>',
            'sender' => $sender,
        ];
    }
}
