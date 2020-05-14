<?php

namespace FKSDB\ORM\Services;

use DuplicateOrgException;
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

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelEventOrg::class;
    }

    /**
     * @return string
     */
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
     * @param ModelEvent $event
     * @return TypedTableSelection
     */
    public function findByEvent(ModelEvent $event): TypedTableSelection {
        return $this->getTable()->where('event_id', $event->event_id);
    }
}
