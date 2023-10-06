<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\StudyYear;
use Nette\Forms\Form;

class FOLSchoolCheckProcessing extends FormProcessing
{

    public function __invoke(array $values, Form $form, EventModel $event): array
    {
        $members = TeamFormComponent::getMembersFromForm($form);
        if (!count($members)) {
            throw new NoMemberException();
        }
        foreach ($members as $member) {
            $history = $member->getHistoryByContestYear($event->getContestYear());
            if ($history->study_year_new->value !== StudyYear::None && !isset($history->school_id)) {
                throw new SchoolRequiredException($history->study_year_new, $member);
            }
        }
        return $values;
    }
}
