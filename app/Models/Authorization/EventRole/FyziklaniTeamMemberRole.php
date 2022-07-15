<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\ModelEvent;

class FyziklaniTeamMemberRole extends EventRole
{
    public TeamMemberModel $member;

    public function __construct(ModelEvent $event, TeamMemberModel $member)
    {
        parent::__construct('event.fyziklaniTeamMember', $event);
        $this->member = $member;
    }
}
