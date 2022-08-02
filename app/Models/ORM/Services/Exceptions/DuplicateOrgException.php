<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Exceptions;

use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\Models\PersonModel;

class DuplicateOrgException extends ModelException
{

    public function __construct(PersonModel $person = null, ?\Throwable $previous = null)
    {
        $message = sprintf(_('Person %s is already organiser'), $person ? $person->getFullName() : _('Person'));
        parent::__construct($message, null, $previous);
    }
}
