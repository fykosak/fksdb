<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\EventModel;
use Nette\Security\Resource;

final class EventResourceHolder implements ResourceHolder
{
    /** @var string|Resource */
    private $resource;
    private EventModel $event;
    /**
     * @param string|Resource $resource
     */
    private function __construct($resource, EventModel $event)
    {
        $this->resource = $resource;
        $this->event = $event;
    }

    public static function fromOwnResource(EventResource $resource): self
    {
        return new self($resource, $resource->getEvent());
    }

    public static function fromResource(Resource $resource, EventModel $event): self
    {
        if ($resource instanceof EventResource) {
            throw new \InvalidArgumentException();
        }
        return new self($resource, $event);
    }

    public static function fromResourceId(string $resourceId, EventModel $event): self
    {
        return new self($resourceId, $event);
    }

    public function getContext(): EventModel
    {
        return $this->event;
    }

    public function getResourceId(): string
    {
        return is_string($this->resource) ? $this->resource : $this->resource->getResourceId();
    }

    /**
     * @return Resource|string
     */
    public function getResource()
    {
        return $this->resource;
    }
}
