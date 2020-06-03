<?php

namespace EventModule;

use FKSDB\EntityTrait;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

/**
 * Trait EventEntityTrait
 * *
 */
trait EventEntityTrait {
    use EntityTrait {
        loadEntity as loadBaseEntity;
    }

    /**
     * @param int $id
     * @return AbstractModelMulti|AbstractModelSingle
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function loadEntity(int $id) {
        $this->loadBaseEntity($id);

        if (!$this->model instanceof IEventReferencedModel) {
            throw new BadTypeException(IEventReferencedModel::class, $this->model);
        }
        if ($this->model->getEvent()->event_id !== $this->getEvent()->event_id) {
            throw new ForbiddenRequestException();
        }

        return $this->model;
    }

    /**
     * @return ModelEvent
     * @throws BadRequestException
     * @throws AbortException
     */
    abstract protected function getEvent(): ModelEvent;

}
