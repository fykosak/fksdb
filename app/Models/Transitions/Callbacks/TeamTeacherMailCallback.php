<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\Transitions\Holder\FyziklaniTeamHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class TeamTeacherMailCallback extends MailCallback
{
    protected function getPersonFromHolder(ModelHolder $holder): array
    {
        if (!$holder instanceof FyziklaniTeamHolder) {
            throw new BadTypeException(FyziklaniTeamHolder::class, $holder);
        }
        $persons = [];
        /** @var TeamTeacherModel $teacher */
        foreach ($holder->getModel()->getTeachers() as $teacher) {
            $persons[] = $teacher->person;
        }
        return $persons;
    }
}
