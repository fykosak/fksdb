<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Models\ORM\Models\EventModel;

interface EventRelation
{
    public function getEvent(EventModel $event): EventModel;
}
