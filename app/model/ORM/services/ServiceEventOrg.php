<?php

use ORM\IModel;

class ServiceEventOrg extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_EVENT_ORG;
    protected $modelClassName = 'FKSDB\ORM\ModelEventOrg';

    public function save(IModel &$model) {
        try {
            parent::save($model);
        } catch (ModelException $e) {
            if ($e->getPrevious() && $e->getPrevious()->getCode() == 23000) {
                throw new DuplicateOrgException($model->getPerson(), $e);
            }
            throw $e;
        }
    }

    public function findByEventID($eventID) {
        return $this->getTable()->where('event_id', $eventID);
    }
}
