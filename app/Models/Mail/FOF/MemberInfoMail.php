<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\FOF;

use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\Transitions\Holder\TeamHolder;

class MemberInfoMail extends InfoEmail
{
    protected function getTemplatePath(TeamHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'member.' . $holder->getState()->value;
    }

    protected function getData(TeamHolder $holder): array
    {
        if ($holder->getModel()->game_lang->value === 'cs') {
            $subject = 'Úprava tímu – ' . $holder->getModel()->name;
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

    final protected function getPersons(TeamHolder $holder): array
    {
        $persons = [];
        /** @var TeamMemberModel $member */
        foreach ($holder->getModel()->getMembers() as $member) {
            $persons[] = $member->person;
        }
        return $persons;
    }
}
