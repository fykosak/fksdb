<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
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
        if (!$holder instanceof ContestResourceHolder) {
            throw new WrongAssertionException();
        }
        $contest = $holder->getContext();
        $person = $holder->getResource();
        if (!$person instanceof PersonModel) {
            throw new WrongAssertionException();
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
