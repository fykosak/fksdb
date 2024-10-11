<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\ContestYearModel;
use Nette\Security\Resource;

final class ContestYearResourceHolder implements ResourceHolder
{
    /** @var string|Resource */
    private $resource;
    private ContestYearModel $contestYear;

    /**
     * @param string|Resource $resource
     */
    private function __construct($resource, ContestYearModel $contestYear)
    {
        $this->resource = $resource;
        $this->contestYear = $contestYear;
    }

    public static function fromOwnResource(ContestYearResource $model): self
    {
        return new self($model, $model->getContestYear());
    }

    public static function fromResource(Resource $resource, ContestYearModel $contestYear): self
    {
        if ($resource instanceof ContestYearResource) {
            throw new \InvalidArgumentException();
        }
        return new self($resource, $contestYear);
    }

    public static function fromResourceId(string $resourceId, ContestYearModel $contestYearModel): self
    {
        return new self($resourceId, $contestYearModel);
    }

    public function getContext(): ContestYearModel
    {
        return $this->contestYear;
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
