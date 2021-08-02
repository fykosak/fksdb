<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Services\Exceptions\DuplicateApplicationException;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Fykosak\NetteORM\AbstractModel;
use Fykosak\NetteORM\Exceptions\ModelException;

class ServiceEventParticipant extends OldAbstractServiceSingle
{

    public function storeModel(array $data, ?AbstractModel $model = null): AbstractModel
    {
        try {
            return parent::storeModel($data, $model);
        } catch (ModelException $exception) {
            if ($exception->getPrevious() && $exception->getPrevious()->getCode() == 23000) {
                throw new DuplicateApplicationException($model->getPerson(), $exception);
            }
            throw $exception;
        }
    }

    public function createNewModel(array $data): ModelEventParticipant
    {
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
     * @param AbstractModel|ModelEventParticipant $model
     */
    public function dispose(AbstractModel $model): void
    {
        $person = $model->getPerson();
        if ($person) {
            $person->removeScheduleForEvent($model->event_id);
        }
        parent::dispose($model);
    }
}
