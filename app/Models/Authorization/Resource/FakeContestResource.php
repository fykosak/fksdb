<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\ContestModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Security\Resource;

final class FakeContestResource implements ContestResource
{
    /** @var Resource&Model $resource */
    private Resource $model;
    private ContestModel $contest;

    /**
     * @param Resource&Model $model
     */
    public function __construct(Resource $model, ContestModel $contest)
    {
        $this->model = $model;
        $this->contest = $contest;
    }

    public function getContest(): ContestModel
    {
        return $this->contest;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getResourceId(): string
    {
        return $this->model->getResourceId();
    }
}