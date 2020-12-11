<?php

namespace FKSDB\Model\ORM\Services\Exceptions;

use FKSDB\Model\ORM\Models\ModelPerson;
use Fykosak\Utils\ORM\Exceptions\ModelException;

class DuplicateOrgException extends ModelException {
    public function __construct(ModelPerson $person = null, ?\Throwable $previous = null) {
        $message = sprintf(_('Person %s is already organiser'), $person ? $person->getFullName() : _('Person'));
        parent::__construct($message, null, $previous);
    }
}
