<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\FOF;

use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\TeamHolder;

class TeacherInfoMail extends InfoEmail
{
    protected function getTemplatePath(TeamHolder $holder): string
    {
        $lang = $holder->getModel()->game_lang->value;
        return __DIR__ . DIRECTORY_SEPARATOR . "teacher.info.$lang.latte";
    }

    protected function getData(TeamHolder $holder): array
    {
        return MemberTransitionMail::getStaticData($holder);
    }

    protected function getPersons(TeamHolder $holder): array
    {
        return TeacherTransitionMail::getTeacherPersons($holder);
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
