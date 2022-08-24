<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\Transitions\Holder\FyziklaniTeamHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class TeamMemberMailCallback extends MailCallback
{
    protected function getPersonFromHolder(ModelHolder $holder): array
    {
        if (!$holder instanceof FyziklaniTeamHolder) {
            throw new BadTypeException(FyziklaniTeamHolder::class, $holder);
        }
        $persons = [];
        /** @var TeamMemberModel $member */
        foreach ($holder->getModel()->getMembers() as $member) {
            $persons[] = $member->person;
        }
        return $persons;
    }
}
