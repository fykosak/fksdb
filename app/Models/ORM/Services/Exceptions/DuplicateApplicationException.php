<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Exceptions;

use FKSDB\Models\ORM\Models\PersonModel;

class DuplicateApplicationException extends \PDOException
{
    public function __construct(?PersonModel $person = null, ?\Throwable $previous = null)
    {
        $message = sprintf(
            _('Person %s is already applied to the event.'),
            $person ? $person->getFullName() : _('Person')
        );
        parent::__construct($message, 0, $previous);
    }
}
