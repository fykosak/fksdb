<?php

namespace FKSDB\Models\ORM\Services\Exceptions;


use FKSDB\Models\ORM\Models\ModelPerson;
use Fykosak\Utils\ORM\Exceptions\ModelException;


class DuplicateApplicationException extends ModelException {

    public function __construct(?ModelPerson $person = null, ?\Throwable $previous = null) {
        $message = sprintf(_('Person %s is already applied to the event.'), $person ? $person->getFullName() : _('Person'));
        parent::__construct($message, null, $previous);
    }
}
