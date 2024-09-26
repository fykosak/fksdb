<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\ContestModel;
use Nette\Security\Resource;

final class ContestResourceHolder implements ResourceHolder
{
    /** @var string|Resource $resource */
    private $resource;
    private ContestModel $contest;

    /**
     * @param string|Resource $resource
     */
    private function __construct($resource, ContestModel $contest)
    {
        $this->resource = $resource;
        $this->contest = $contest;
    }

    public static function fromOwnResource(ContestResource $resource): self
    {
        return new self($resource, $resource->getContest());
    }

    public static function fromResource(Resource $resource, ContestModel $contest): self
    {
        if ($resource instanceof ContestResource) {
            throw new \InvalidArgumentException();
        }
        return new self($resource, $contest);
    }

    public static function fromResourceId(string $resourceId, ContestModel $contest): self
    {
        return new self($resourceId, $contest);
    }

    public function getContext(): ContestModel
    {
        return $this->contest;
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
