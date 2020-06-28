<?php

use FKSDB\Exceptions\ModelException;
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
    public function __construct(ModelPerson $person = null, $previous = null) {
        $message = sprintf(_('Osoba %s je na akci již přihlášena.'), $person ? $person->getFullName() : _('Person'));
        parent::__construct($message, null, $previous);
    }
}
