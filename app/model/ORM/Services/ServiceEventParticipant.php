<?php

namespace FKSDB\ORM\Services;

use DuplicateApplicationException;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelEventParticipant;
use ModelException;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceEventParticipant extends AbstractServiceSingle {

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelEventParticipant::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_EVENT_PARTICIPANT;
    }

    /**
     * @param IModel|ModelEventParticipant $model
     */
    public function save(IModel &$model) {
        try {
            parent::save($model);
        } catch (ModelException $exception) {
            if ($exception->getPrevious() && $exception->getPrevious()->getCode() == 23000) {
                throw new DuplicateApplicationException($model->getPerson(), $exception);
            }
            throw $exception;
        }
    }

    /**
     * @param IModel $model
     * @param array $data
     * @param bool $alive
     * @return mixed|void
     * @deprecated
     */
    public function updateModel(IModel $model, $data, $alive = true) {
        /**
         * @var \FKSDB\ORM\Models\ModelEventParticipant $model
         */
        parent::updateModel($model, $data, $alive);
        if (!$alive && !$model->isNew()) {
            $person = $model->getPerson();
            if ($person) {
                $person->removeAccommodationForEvent($model->event_id);
            }

        }
    }
}
