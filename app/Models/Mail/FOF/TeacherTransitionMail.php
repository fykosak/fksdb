<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\FOF;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends MailCallback<TeamModel2>
 */
class TeacherTransitionMail extends MailCallback
{
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        $transitionId = self::resolveLayoutName($transition);
        $lang = $holder->getModel()->game_lang->value;
        return __DIR__ . DIRECTORY_SEPARATOR . "teacher.$transitionId.$lang.latte";
    }

    /**
     * @param TeamHolder $holder
     */
    protected function getData(ModelHolder $holder): array
    {
        return MemberTransitionMail::getStaticData($holder);
    }

    /**
     * @param TeamHolder $holder
     * @throws BadTypeException
     */
    protected function getPersons(ModelHolder $holder): array
    {
        if (!$holder instanceof TeamHolder) {
            throw new BadTypeException(TeamHolder::class, $holder);
        }
        return self::getTeacherPersons($holder);
    }

    /**
     * @param TeamModel2 $model
     * @throws BadTypeException
     */
    protected function createToken(PersonModel $person, Model $model): AuthTokenModel
    {
        if (!$model instanceof TeamModel2) {
            throw new BadTypeException(TeamModel2::class, $model);
        }
        return $this->authTokenService->createToken(
            $this->resolveLogin($person),
            AuthTokenType::from(AuthTokenType::EVENT_NOTIFY),
            $model->event->registration_end,
            null,
            true
        );
    }

    /**
     * @return PersonModel[]
     */
    public static function getTeacherPersons(TeamHolder $holder): array
    {
        $persons = [];
        /** @var TeamTeacherModel $teacher */
        foreach ($holder->getModel()->getTeachers() as $teacher) {
            $persons[] = $teacher->person;
        }
        return $persons;
    }
}
