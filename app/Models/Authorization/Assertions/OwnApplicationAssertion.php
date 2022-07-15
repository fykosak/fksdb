<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\EventRole\FyziklaniTeamTeacherRole;
use FKSDB\Models\Authorization\EventRole\FyziklaniTeamMemberRole;
use FKSDB\Models\Authorization\EventRole\ParticipantRole;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Nette\Security\Permission;
use Nette\Security\Role;

class OwnApplicationAssertion implements Assertion
{
    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        $queriedRole = $acl->getQueriedRole();
        $application = $acl->getQueriedResource();
        if ($application instanceof TeamModel2) {
            return $this->isTeamMember($queriedRole, $application);
        } elseif ($application instanceof ModelEventParticipant) {
            if ($queriedRole instanceof ParticipantRole) {
                return $queriedRole->eventParticipant->event_participant_id === $application->event_participant_id;
            }
        }
        return false;
    }

    private function isTeamMember(Role $role, TeamModel2 $application): bool
    {
        if ($role instanceof FyziklaniTeamTeacherRole) {
            foreach ($role->teams as $team) {
                if ($team->fyziklani_team_id === $application->fyziklani_team_id) {
                    return true;
                }
            }
        } elseif ($role instanceof FyziklaniTeamMemberRole) {
            if ($role->member->fyziklani_team_id === $application->fyziklani_team_id) {
                return true;
            }
        }
        return false;
    }
}
