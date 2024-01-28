<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani\Processing\SchoolRequirement;

use FKSDB\Components\EntityForms\Fyziklani\NoMemberException;
use FKSDB\Components\EntityForms\Fyziklani\Processing\FormProcessing;
use FKSDB\Components\EntityForms\Fyziklani\TeamForm;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\StudyYear;
use Nette\Forms\Form;

class FOLSchoolRequirementProcessing extends FormProcessing
{
    public function __invoke(array $values, Form $form, EventModel $event, ?TeamModel2 $model): array
    {
        $members = TeamForm::getFormMembers($form);
        if (!count($members)) {
            throw new NoMemberException();
        }
        foreach ($members as $member) {
            $history = $member->getHistory($event->getContestYear());
            if ($history->study_year_new->value !== StudyYear::None && !isset($history->school_id)) {
                throw new SchoolRequirementProcessingException($history->study_year_new, $member);
            }
        }
        return $values;
    }
}
