<?php

use FKSDB\ORM\Models\ModelEvent;
use ORM\IModel;

/**
 * Class ServiceEventOrg
 */
class ServiceEventOrg extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_EVENT_ORG;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelEventOrg';

    /**
     * @param IModel $model
     * @return mixed|void
     */
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

    /**
     * @param ModelEvent $event
     * @return \Nette\Database\Table\Selection
     */
    public function findByEventId(ModelEvent $event) {
        return $this->getTable()->where('event_id', $event->event_id);
    }
}
