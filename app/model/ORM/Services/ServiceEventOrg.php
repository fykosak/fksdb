<?php

namespace FKSDB\ORM\Services;

use DuplicateOrgException;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelEvent;
use ModelException;
use Nette\Database\Table\Selection;

/**
 * Class FKSDB\ORM\Services\ServiceEventOrg
 */
class ServiceEventOrg extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_EVENT_ORG;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelEventOrg';

    /**
     * @param \FKSDB\ORM\IModel $model
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
     * @return Selection
     */
    public function findByEventId(ModelEvent $event): Selection {
        return $this->getTable()->where('event_id', $event->event_id);
    }
}
