<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\Authorization\Resource\ContestYearResourceHolder;
use FKSDB\Models\Authorization\Roles\Contest\ContestRole;
use FKSDB\Models\Authorization\Roles\ContestYear\ContestYearRole;
use FKSDB\Models\Authorization\Roles\Events\EventRole;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\Security\Permission;

class ContestRelatedAssertion implements Assertion
{
    /**
     * Checks whether person is contestant in any of the role-assigned contests.
     */
    public function __invoke(Permission $acl): bool
    {
        $holder = $acl->getQueriedResource();
        $person = $holder->getResource();

        if (!$person instanceof PersonModel) {
            throw new WrongAssertionException();
        }
        $role = $acl->getQueriedRole();
        if ($role instanceof ContestYearRole) {
            $contest = $role->getContestYear()->contest;
        } elseif ($role instanceof ContestRole) {
            $contest = $role->getContest();
        } elseif ($role instanceof EventRole) {
            $contest = $role->getEvent()->event_type->contest;
        } else {
            return false;
        }

        if ($person->getContestants($contest)->fetch()) {
            return true;
        }
        if ($person->getOrganizer($contest)) {
            return true;
        }
        /** @var EventParticipantModel $participant */
        foreach ($person->getEventParticipants() as $participant) {
            if ($participant->event->event_type->contest_id === $contest->contest_id) {
                return true;
            }
        }
        /** @var TeamMemberModel $member */
        foreach ($person->getTeamTeachers() as $member) {
            if ($member->fyziklani_team->event->event_type->contest_id === $contest->contest_id) {
                return true;
            }
        }
        /** @var TeamTeacherModel $teacher */
        foreach ($person->getTeamTeachers() as $teacher) {
            if ($teacher->fyziklani_team->event->event_type->contest_id === $contest->contest_id) {
                return true;
            }
        }
        return false;
    }
}
