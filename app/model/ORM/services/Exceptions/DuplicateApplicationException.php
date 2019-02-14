<?php

use FKSDB\ORM\ModelPerson;

/**
 * Class DuplicateApplicationException
 */
class DuplicateApplicationException extends ModelException {

    /**
     * DuplicateApplicationException constructor.
     * @param ModelPerson $person
     * @param null $previous
     */
    public function __construct(ModelPerson $person, $previous = null) {
        $message = sprintf(_('Osoba %s je na akci již přihlášena.'), $person->getFullName());
        parent::__construct($message, null, $previous);
    }
}
