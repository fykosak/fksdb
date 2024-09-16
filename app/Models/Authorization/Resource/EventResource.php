<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\EventModel;

interface EventResource extends ContestYearResource
{
    public function getEvent(): EventModel;
}
