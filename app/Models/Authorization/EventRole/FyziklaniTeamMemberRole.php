<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\EventModel;

class FyziklaniTeamMemberRole extends EventRole
{
    public TeamMemberModel $member;

    public function __construct(EventModel $event, TeamMemberModel $member)
    {
        parent::__construct('event.fyziklaniTeamMember', $event);
        $this->member = $member;
    }
}
