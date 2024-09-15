<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\EventModel;
use Nette\Security\Resource;

interface EventResource extends Resource
{
    public function getEvent(): EventModel;
}