<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Services\Exceptions\DuplicateApplicationException;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\Exceptions\ModelException;

class ServiceEventParticipant extends OldServiceSingle
{

    public function storeModel(array $data, ?Model $model = null): Model
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
     * @param ModelEventParticipant $model
     */
    public function dispose(Model $model): void
    {
        $person = $model->getPerson();
        if ($person) {
            $person->removeScheduleForEvent($model->getEvent());
        }
        parent::dispose($model);
    }
}
