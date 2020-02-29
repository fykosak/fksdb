<?php

namespace EventModule;

use FKSDB\EntityTrait;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

/**
 * Trait EventEntityTrait
 * @package EventModule
 */
trait EventEntityTrait {
    use EntityTrait {
        loadEntity as loadBaseEntity;
    }

    /**
     * @param int $id
     * @return mixed
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function loadEntity(int $id) {
        $this->loadBaseEntity($id);

        if (!$this->model instanceof IEventReferencedModel) {
            throw new BadRequestException('Model must be a instance of IEventReferencedModel', 500);
        }
        if ($this->model->getEvent()->event_id !== $this->getEvent()->event_id) {
            throw new ForbiddenRequestException();
        }

        return $this->model;
    }

    /**
     * @param $resource
     * @param $privilege
     * @return bool
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function isAllowed($resource, $privilege): bool {
        return $this->eventIsAllowed($resource, $privilege);
    }

    /**
     * @return ModelEvent
     * @throws BadRequestException
     * @throws AbortException
     */
    abstract protected function getEvent(): ModelEvent;

    /**
     * @param $resource
     * @param $privilege
     * @return bool
     * @throws BadRequestException
     * @throws AbortException
     */
    abstract protected function eventIsAllowed($resource, $privilege): bool;
}
