<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\FOF;

use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\Fyziklani\GameLang;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use Nette\InvalidStateException;

class TeacherInfoMail extends InfoEmail
{
    protected function getTemplatePath(TeamHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'teacher.info';
    }

    protected function getData(TeamHolder $holder): array
    {
        switch ($holder->getModel()->game_lang->value) {
            case GameLang::CS:
                $sender = 'Fyziklání <fyziklani@fykos.cz>';
                break;
            case GameLang::EN:
                $sender = 'Fyziklani <fyziklani@fykos.cz>';
                break;
            default:
                throw new InvalidStateException();
        }
        return [
            'sender' => $sender,
        ];
    }

    protected function getPersons(TeamHolder $holder): array
    {
        $persons = [];
        /** @var TeamTeacherModel $teacher */
        foreach ($holder->getModel()->getTeachers() as $teacher) {
            $persons[] = $teacher->person;
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
