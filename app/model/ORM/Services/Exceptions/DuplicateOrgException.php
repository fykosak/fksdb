<?php

namespace FKSDB\ORM\Services\Exceptions;

use FKSDB\Exceptions\ModelException;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class DuplicateOrgException
 */
class DuplicateOrgException extends ModelException {

    public function __construct(ModelPerson $person = null, ?\Throwable $previous = null) {
        $message = sprintf(_('Person %s is already organiser'), $person ? $person->getFullName() : _('Person'));
        parent::__construct($message, null, $previous);
    }
}
