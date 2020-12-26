<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Services\Exceptions\DuplicateApplicationException;

use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\Exceptions\ModelException;
use FKSDB\Models\ORM\Tables\TypedTableSelection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceEventParticipant extends AbstractServiceSingle {

    /**
     * @param ModelEventParticipant|IModel $model
     * @throws DuplicateApplicationException
     * @throws ModelException
     * @deprecated
     */
    public function save(IModel &$model): void {
        try {
            parent::save($model);
        } catch (ModelException $exception) {
            if ($exception->getPrevious() && $exception->getPrevious()->getCode() == 23000) {
                throw new DuplicateApplicationException($model->getPerson(), $exception);
            }
            throw $exception;
        }
    }

    public function createNewModel(array $data): ModelEventParticipant {
        try {
            return parent::createNewModel($data);
        } catch (ModelException $exception) {
            if ($exception->getPrevious() && $exception->getPrevious()->getCode() == 23000) {
                throw new DuplicateApplicationException(null, $exception);
            }
            throw $exception;
        }
    }

    /**
     * @param IModel|ModelEventParticipant $model
     * @param array $data
     * @param bool $alive
     * @return void
     * @deprecated
     */
    public function updateModel(IModel $model, $data, $alive = true): void {
        parent::updateModel($model, $data, $alive);
        if (!$alive && !$model->isNew()) {
            $person = $model->getPerson();
            if ($person) {
                $person->removeScheduleForEvent($model->event_id);
            }
        }
    }

    public function findPossiblyAttending(ModelEvent $event): TypedTableSelection {
        return $this->findByEvent($event)->where('status', ['participated', 'approved', 'spare', 'applied']);
    }

    public function findByEvent(ModelEvent $event): TypedTableSelection {
        return $this->getTable()->where('event_id', $event->event_id);
    }
}
