<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani\Processing;

use FKSDB\Components\EntityForms\Fyziklani\TeamFormComponent;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\Forms\Form;

class SchoolsPerTeamProcessing extends FormProcessing
{
    /**
     * @phpstan-template TValue of array<string,mixed>
     * @phpstan-param TValue $values
     * @phpstan-return TValue
     */
    public function __invoke(array $values, Form $form, EventModel $event): array
    {
        $members = TeamFormComponent::getFormMembers($form);
        $schools = [];
        foreach ($members as $member) {
            $school = $member->getHistoryByContestYear($event->getContestYear())->school;
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
