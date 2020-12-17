<?php

namespace FKSDB\Model\ORM\Services\Exceptions;

use FKSDB\Model\Exceptions\ModelException;
use FKSDB\Model\ORM\Models\ModelPerson;

/**
 * Class DuplicateApplicationException
 */
class DuplicateApplicationException extends ModelException {

    public function __construct(?ModelPerson $person = null, ?\Throwable $previous = null) {
        $message = sprintf(_('Person %s is already applied to the event.'), $person ? $person->getFullName() : _('Person'));
        parent::__construct($message, null, $previous);
    }
}