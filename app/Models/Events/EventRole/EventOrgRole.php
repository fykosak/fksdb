<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\EventRole;

use FKSDB\Models\ORM\Models\ModelEventOrg;

class EventOrgRole implements EventRole
{

    public ModelEventOrg $eventOrg;

    public function __construct(ModelEventOrg $eventOrg)
    {
        $this->eventOrg = $eventOrg;
    }
}
