<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\ContestModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Security\Resource;

final class PseudoContestResource implements ContestResource
{
    /** @var string|(Model&Resource) $resource */
    private $resource;
    private ContestModel $contest;

    /**
     * @param string|(Model&Resource) $resource
     */
    public function __construct($resource, ContestModel $contest)
    {
        $this->resource = $resource;
        $this->contest = $contest;
    }

    public function getContest(): ContestModel
    {
        return $this->contest;
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
