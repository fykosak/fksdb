<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\OrgModel;

class ContestOrgRole extends EventRole
{
    public OrgModel $org;

    public function __construct(EventModel $event, OrgModel $org)
    {
        parent::__construct('event.contestOrg', $event);
        $this->org = $org;
    }
}
