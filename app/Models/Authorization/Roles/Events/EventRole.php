<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Events;

use FKSDB\Models\Authorization\Roles\Role;
use FKSDB\Models\ORM\Models\EventModel;

interface EventRole extends Role
{
    public function getEvent(): EventModel;
}
