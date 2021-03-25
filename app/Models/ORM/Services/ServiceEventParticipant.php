<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\OldAbstractModelSingle;
use FKSDB\Models\ORM\Services\Exceptions\DuplicateApplicationException;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Fykosak\NetteORM\AbstractModel;
use Fykosak\NetteORM\Exceptions\ModelException;
use Nette\Database\Table\ActiveRow;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceEventParticipant extends OldAbstractServiceSingle {

    public function store(?AbstractModel $model, array $data): OldAbstractModelSingle {
        try {
            return parent::store($model, $data);
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
     * @param ActiveRow|ModelEventParticipant $model
     * @param iterable|null $data
     * @param bool $alive
     * @return void
     * @deprecated
     */
    public function updateModel(ActiveRow $model, ?iterable $data, $alive = true): void {
        parent::updateModel($model, $data, $alive);
        if (!$alive && !$model->isNew()) {
            $person = $model->getPerson();
            if ($person) {
                $person->removeScheduleForEvent($model->event_id);
            }
        }
    }
}
