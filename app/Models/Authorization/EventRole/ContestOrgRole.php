<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\OrgModel;

class ContestOrgRole extends EventRole
{
    public OrgModel $organiser;

    public function __construct(EventModel $event, OrgModel $organiser)
    {
        parent::__construct('event.contestOrg', $event);
        $this->organiser = $organiser;
    }
}
