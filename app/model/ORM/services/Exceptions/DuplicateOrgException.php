<?php

class DuplicateOrgException extends ModelException {

    public function __construct(ModelPerson $person, $previous = null) {
        $message = sprintf(_('Osoba %s je na akci již přihlášena.'), $person->getFullname());
        parent::__construct($message, null, $previous);
    }
}
