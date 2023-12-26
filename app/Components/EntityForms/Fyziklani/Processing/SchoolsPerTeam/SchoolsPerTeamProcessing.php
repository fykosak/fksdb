<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani\Processing\SchoolsPerTeam;

use FKSDB\Components\EntityForms\Fyziklani\Processing\FormProcessing;
use FKSDB\Components\EntityForms\Fyziklani\TeamForm;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Forms\Form;

class SchoolsPerTeamProcessing extends FormProcessing
{
    /**
     * @phpstan-template TValue of array<string,mixed>
     * @phpstan-param TValue $values
     * @phpstan-return TValue
     */
    public function __invoke(array $values, Form $form, EventModel $event, ?TeamModel2 $model): array
    {
        $members = TeamForm::getFormMembers($form);
        $schools = [];
        foreach ($members as $member) {
            $school = $member->getHistory($event->getContestYear())->school;
            if (!isset($schools[$school->school_id])) {
                $schools[$school->school_id] = $school;
            }
        }
        if (count($schools) > 2) {
            throw new SchoolsPerTeamException($schools);
        }
        return $values;
    }
}
