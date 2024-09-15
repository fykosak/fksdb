<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\ContestYearModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Security\Resource;

final class PseudoContestYearResource implements ContestYearResource
{
    /** @var string|(Model&Resource) */
    private $resource;
    private ContestYearModel $contestYear;

    /**
     * @param string|(Model&Resource) $resource
     */
    public function __construct($resource, ContestYearModel $contestYear)
    {
        $this->resource = $resource;
        $this->contestYear = $contestYear;
    }

    public function getContestYear(): ContestYearModel
    {
        return $this->contestYear;
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
