<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\FOF;

use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\Transitions\Holder\TeamHolder;

class TeacherInfoMail extends InfoEmail
{
    protected function getTemplatePath(TeamHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'teacher.' . $holder->getState()->value;
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

    protected function getPersons(TeamHolder $holder): array
    {
        $persons = [];
        /** @var TeamTeacherModel $teacher */
        foreach ($holder->getModel()->getTeachers() as $teacher) {
            $persons[] = $teacher->person;
        }
        return $persons;
    }
}
