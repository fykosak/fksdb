<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\EventRole;

use FKSDB\Models\ORM\Models\ModelOrg;

class ContestOrgRole implements EventRole
{
    public ModelOrg $org;

    public function __construct(ModelOrg $org)
    {
        $this->org = $org;
    }

}
