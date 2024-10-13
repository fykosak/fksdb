<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use Nette\Security\Resource;

final class BaseResourceHolder implements ResourceHolder
{
    /** @var string|Resource $resource */
    private $resource;

    /**
     * @param string|Resource $resource
     */
    private function __construct($resource)
    {
        $this->resource = $resource;
    }

    public static function fromObject(Resource $model): self
    {
        return new self($model);
    }

    public static function fromString(string $resourceId): self
    {
        return new self($resourceId);
    }

    /**
     * @return Resource|string
     */
    public function getResource()
    {
        return $this->resource;
    }

    public function getResourceId(): string
    {
        return is_string($this->resource) ? $this->resource : $this->resource->getResourceId();
    }
}
