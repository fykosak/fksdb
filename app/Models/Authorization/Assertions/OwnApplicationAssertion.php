<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\EventRole\FyziklaniTeacherRole;
use FKSDB\Models\Authorization\EventRole\ParticipantRole;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Nette\Security\Permission;
use Nette\Security\Role;

class OwnApplicationAssertion implements Assertion
{
    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        $queriedRole = $acl->getQueriedRole();
        $application = $acl->getQueriedResource();
        if ($application instanceof ModelFyziklaniTeam) {
            return $this->isTeamMember($queriedRole, $application);
        } elseif ($application instanceof ModelEventParticipant) {
            if ($queriedRole instanceof ParticipantRole) {
                return $queriedRole->eventParticipant->event_participant_id === $application->event_participant_id;
            }
        }
        return false;
    }

    private function isTeamMember(Role $role, ModelFyziklaniTeam $application): bool
    {
        if ($role instanceof FyziklaniTeacherRole) {
            foreach ($role->teams as $team) {
                if ($team->e_fyziklani_team_id === $application->e_fyziklani_team_id) {
                    return true;
                }
            }
        } elseif ($role instanceof ParticipantRole) {
            $team = $role->eventParticipant->getFyziklaniTeam();
            if ($team && $team->e_fyziklani_team_id === $application->e_fyziklani_team_id) {
                return true;
            }
        }
        return false;
    }
}
