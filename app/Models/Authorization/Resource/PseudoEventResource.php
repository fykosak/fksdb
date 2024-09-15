<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\Model\Model;

final class PseudoEventResource implements EventResource
{
    /** @var string|(Model&Resource) */
    private $resource;
    private EventModel $event;
    /**
     * @param string|(Model&Resource) $resource
     */
    public function __construct(string $resource, EventModel $event)
    {
        $this->resource = $resource;
        $this->event = $event;
    }

    public function getEvent(): EventModel
    {
        return $this->event;
    }

    public function getModel(): ?Model
    {
        return $this->resource instanceof Model ? $this->resource : null;
    }

    public function getResourceId(): string
    {
        return is_string($this->resource) ? $this->resource : $this->resource->getResourceId();
    }
}
