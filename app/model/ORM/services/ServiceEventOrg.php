<?php

use ORM\CachingServiceTrait;
use ORM\IModel;

class ServiceEventOrg extends AbstractServiceSingle {
    use CachingServiceTrait;

    protected $tableName = DbNames::TAB_EVENT_ORG;
    protected $modelClassName = 'ModelEventOrg';

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

class DuplicateOrgException extends ModelException {

    public function __construct(ModelPerson $person, $previous = null) {
        $message = sprintf(_('Osoba %s je na akci již přihlášena.'), $person->getFullname());
        parent::__construct($message, null, $previous);
    }
}
