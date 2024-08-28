<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use Nette\Security\Permission;

class NotDisqualified implements Assertion
{
    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        $application = $acl->getQueriedResource();
        if (!$application instanceof TeamModel2) {
            return false;
        }
        return $application->state->value !== TeamState::Disqualified;
    }
}
