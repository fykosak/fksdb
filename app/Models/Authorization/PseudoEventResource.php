<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use FKSDB\Models\ORM\Models\EventModel;
use Nette\Security\Resource;

final class PseudoEventResource implements Resource
{
    private string $resourceId;
    public EventModel $event;

    public function __construct(string $resourceId, EventModel $event)
    {
        $this->resourceId = $resourceId;
        $this->event = $event;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }
}