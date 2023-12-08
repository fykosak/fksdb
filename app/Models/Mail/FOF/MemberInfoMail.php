<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\FOF;

use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\TeamHolder;

class MemberInfoMail extends InfoEmail
{
    protected function getTemplatePath(TeamHolder $holder): string
    {
        $lang = $holder->getModel()->game_lang->value;
        return __DIR__ . DIRECTORY_SEPARATOR . "member.info.$lang.latte";
    }

    protected function getData(TeamHolder $holder): array
    {
        return MemberTransitionMail::getStaticData($holder);
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

    protected function createToken(PersonModel $person, TeamHolder $holder): AuthTokenModel
    {
        return $this->authTokenService->createToken(
            $this->resolveLogin($person),
            AuthTokenType::from(AuthTokenType::EVENT_NOTIFY),
            $holder->getModel()->event->registration_end,
            null,
            true
        );
    }
}
