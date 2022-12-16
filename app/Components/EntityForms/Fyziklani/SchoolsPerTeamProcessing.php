<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Models\ORM\Models\EventModel;
use Nette\Forms\Form;

class SchoolsPerTeamProcessing extends FormProcessing
{

    public function __invoke(array $values, Form $form, EventModel $event): array
    {
        $members = TeamFormComponent::getMembersFromForm($form);
        $schools = [];
        foreach ($members as $member) {
            $school = $member->getHistory($event->getContestYear()->ac_year)->school;
            if (!isset($schools[$school->school_id])) {
                $schools[$school->school_id] = $school;
            }
        }
        if (count($schools) > 2) {
            throw new TooManySchoolsException($schools);
        }
        return $values;
    }
}
