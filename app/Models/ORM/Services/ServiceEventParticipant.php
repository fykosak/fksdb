<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Services\Exceptions\DuplicateApplicationException;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Fykosak\NetteORM\AbstractModel;
use Fykosak\NetteORM\Exceptions\ModelException;
use Nette\Database\Table\ActiveRow;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceEventParticipant extends OldAbstractServiceSingle {

    public function store(?AbstractModel $model, array $data): AbstractModel {
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
     * @param AbstractModel|ActiveRow|ModelEventParticipant $model
     */
    public function dispose($model): void {
        $person = $model->getPerson();
        if ($person) {
            $person->removeScheduleForEvent($model->event_id);
        }
        parent::dispose($model);
    }
}
