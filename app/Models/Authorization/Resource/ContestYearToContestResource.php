<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\ContestModel;

final class ContestYearToContestResource implements ContestResource
{
    private ContestYearResource $contestYearResource;

    public function __construct(ContestYearResource $contestYearResource)
    {
        $this->contestYearResource = $contestYearResource;
    }

    public function getContest(): ContestModel
    {
        return $this->contestYearResource->getContestYear()->contest;
    }

    public function getResourceId(): string
    {
        return $this->contestYearResource->getResourceId();
    }
}