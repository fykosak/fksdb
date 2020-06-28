<?php

use FKSDB\Exceptions\ModelException;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class DuplicateApplicationException
 */
class DuplicateApplicationException extends ModelException {

    /**
     * DuplicateApplicationException constructor.
     * @param ModelPerson $person
     * @param null $previous
     */
    public function __construct(ModelPerson $person = null, $previous = null) {
        $message = sprintf(_('Osoba %s je na akci již přihlášena.'), $person ? $person->getFullName() : _('Person'));
        parent::__construct($message, null, $previous);
    }
}
