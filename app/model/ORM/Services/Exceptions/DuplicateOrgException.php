<?php

use FKSDB\ORM\Models\ModelPerson;

/**
 * Class DuplicateOrgException
 */
class DuplicateOrgException extends ModelException {

    /**
     * DuplicateOrgException constructor.
     * @param ModelPerson $person
     * @param null $previous
     */
    public function __construct(ModelPerson $person, $previous = null) {
        $message = sprintf(_('Osoba %s je na akci již přihlášena.'), $person->getFullName());
        parent::__construct($message, null, $previous);
    }
}
