<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\TeamHolder;

abstract class TeamMemberMailCallback extends MailCallback
{
    final protected function getPersonsFromHolder(ModelHolder $holder): array
    {
        if (!$holder instanceof TeamHolder) {
            throw new BadTypeException(TeamHolder::class, $holder);
        }
        $persons = [];
        /** @var TeamMemberModel $member */
        foreach ($holder->getModel()->getMembers() as $member) {
            $persons[] = $member->person;
        }
        return $persons;
    }

    /**
     * @throws BadTypeException
     */
    protected function createToken(PersonModel $person, ModelHolder $holder): AuthTokenModel
    {
        if (!$holder instanceof TeamHolder) {
            throw new BadTypeException(TeamHolder::class, $holder);
        }
        return $this->authTokenService->createToken(
            $this->resolveLogin($person),
            AuthTokenType::tryFrom(AuthTokenType::EVENT_NOTIFY),
            $holder->getModel()->event->registration_end,
            null,
            true
        );
    }
}
