<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Services\Exceptions\DuplicateApplicationException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\NetteORM\Service;

class EventParticipantService extends Service
{
    /**
     * @param EventParticipantModel|null $model
     */
    public function storeModel(array $data, ?Model $model = null): EventParticipantModel
    {
        try {
            return parent::storeModel($data, $model);
        } catch (ModelException $exception) {
            if ($exception->getPrevious() && $exception->getPrevious()->getCode() == 23000) {
                throw new DuplicateApplicationException($model ? $model->person : null, $exception);
            }
            throw $exception;
        }
    }

    /**
     * @param EventParticipantModel $model
     */
    public function disposeModel(Model $model): void
    {
        $person = $model->person;
        if ($person) {
            $person->removeScheduleForEvent($model->event);
        }
        parent::disposeModel($model);
    }
}
