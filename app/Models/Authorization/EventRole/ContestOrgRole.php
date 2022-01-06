<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelOrg;

class ContestOrgRole extends EventRole
{
    public ModelOrg $org;

    public function __construct(ModelEvent $event, ModelOrg $org)
    {
        parent::__construct('event.contestOrg', $event);
        $this->org = $org;
    }
}
