<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Security\Resource;

final class FakeEventResource implements EventResource
{
    /** @var Resource&Model $model */
    private Resource $model;
    private EventModel $event;

    /**
     * @param Resource&Model $model
     */
    public function __construct(Resource $model, EventModel $event)
    {
        $this->event = $event;
        $this->model = $model;
    }

    public function getEvent(): EventModel
    {
        return $this->event;
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