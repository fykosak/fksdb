<?php

use ORM\IModel;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceEventParticipant extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_EVENT_PARTICIPANT;
    protected $modelClassName = 'ModelEventParticipant';

    public function save(IModel &$model) {
        try {
            parent::save($model);
        } catch (ModelException $e) {
            if ($e->getPrevious() && $e->getPrevious()->getCode() == 23000) {
                throw new DuplicateApplicationException($model->getPerson(), $e);
            }
            throw $e;
        }
    }

}

class DuplicateApplicationException extends ModelException {

    public function __construct(ModelPerson $person, $previous = null) {
        $message = sprintf(_('Osoba %s je na akci již přihlášena.'), $person->getFullname());
        parent::__construct($message, null, $previous);
    }

}
