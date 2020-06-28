<?php

namespace FKSDB\ORM\Services;

use DuplicateOrgException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\Exceptions\ModelException;
use FKSDB\ORM\Tables\TypedTableSelection;


/**
 * Class FKSDB\ORM\Services\ServiceEventOrg
 */
class ServiceEventOrg extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelEventOrg::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_EVENT_ORG;
    }

    /**
     * @param IModel|ModelEventOrg $model
     * @return void
     * @deprecated
     */
    public function save(IModel &$model) {
        try {
            parent::save($model);
        } catch (ModelException $exception) {
            if ($exception->getPrevious() && $exception->getPrevious()->getCode() == 23000) {
                throw new DuplicateOrgException($model->getPerson(), $exception);
            }
            throw $exception;
        }
    }

    /**
     * @param array $data
     * @return ModelEventOrg
     */
    public function createNewModel(array $data): IModel {
        try {
            return parent::createNewModel($data);
        } catch (ModelException $exception) {
            if ($exception->getPrevious() && $exception->getPrevious()->getCode() == 23000) {
                throw new DuplicateOrgException(null, $exception);
            }
            throw $exception;
        }
    }

    public function findByEvent(ModelEvent $event): TypedTableSelection {
        return $this->getTable()->where('event_id', $event->event_id);
    }
}
