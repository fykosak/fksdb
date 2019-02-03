<?php

use FKSDB\ORM\ModelPerson;

class DuplicateApplicationException extends ModelException {

    public function __construct(ModelPerson $person, $previous = null) {
        $message = sprintf(_('Osoba %s je na akci již přihlášena.'), $person->getFullName());
        parent::__construct($message, null, $previous);
    }
}
